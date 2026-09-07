<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front2026Controller;
use App\Mail\RegistrationConfirmation;
use App\Models\Contribution;
use App\Models\Registration;
use App\Models\Session;
use App\Models\SessionBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;
use Inertia\Response;

/*
 * Who is coming.
 *
 * Registration is per day. Capped sessions are booked separately and counted
 * separately — a place in a workshop is not the same promise as turning up on
 * the day, and conflating them would make both numbers wrong.
 */
class RegistrationsController extends Controller
{
    private const DAYS = [1 => '07', 2 => '08', 3 => '09', 4 => '10'];

    public function index(Request $request): Response
    {
        $day = $request->integer('day') ?: null;
        $status = $request->string('status')->toString();

        $registrations = Registration::query()
            ->when($day, fn ($q) => $q->whereJsonContains('days', $day))
            ->when($status === 'confirmed', fn ($q) => $q->whereNotNull('confirmed_at'))
            ->when($status === 'pending', fn ($q) => $q->whereNull('confirmed_at'))
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Admin/2026/Registrations', [
            'registrations' => $registrations->map(fn (Registration $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'email' => $r->email,
                'organisation' => $r->organisation,
                'country' => $r->country,
                'phone' => $r->phone,
                'days' => $r->days,
                'workshop_interest' => (bool) $r->workshop_interest,
                'locale' => $r->locale,
                'confirmed' => $r->confirmed_at !== null,
                'created' => $r->created_at->toDateTimeString(),
                'sent_count' => $r->sent_count,
            ]),
            'filters' => ['day' => $day, 'status' => $status ?: 'all'],
            'counts' => $this->counts(),
            'publicBase' => Front2026Controller::BASE,
        ]);
    }

    /** Headcounts, which is the question actually being asked of this page. */
    private function counts(): array
    {
        $confirmed = Registration::whereNotNull('confirmed_at');

        $perDay = [];

        foreach (array_keys(self::DAYS) as $day) {
            $perDay[$day] = [
                'date' => self::DAYS[$day],
                'all' => Registration::whereJsonContains('days', $day)->count(),
                'confirmed' => (clone $confirmed)->whereJsonContains('days', $day)->count(),
            ];
        }

        return [
            'total' => Registration::count(),
            'confirmed' => Registration::whereNotNull('confirmed_at')->count(),
            'workshopInterest' => Registration::where('workshop_interest', true)->count(),
            'perDay' => $perDay,
            'contributions' => [
                'count' => Contribution::where('status', 'paid')->count(),
                'total' => Contribution::where('status', 'paid')->sum('amount') / 100,
            ],
        ];
    }

    /**
     * Resends the confirmation email, for the ones that never arrived.
     *
     * Not named `resend`: Laravel resolves a bare action string with a
     * case-insensitive class_exists(), and the Resend SDK owns that name.
     */
    public function resendConfirmation(Registration $registration): RedirectResponse
    {
        if ($registration->isConfirmed()) {
            return back()->with('flash', 'Already confirmed — nothing sent.');
        }

        Mail::to($registration->email)->send(new RegistrationConfirmation($registration));
        $registration->forceFill([
            'last_sent_at' => now(),
            'sent_count' => $registration->sent_count + 1,
        ])->save();

        return back()->with('flash', 'Confirmation resent to '.$registration->email);
    }

    /** Marks an address confirmed by hand, for the ones that never will be. */
    public function confirm(Registration $registration): RedirectResponse
    {
        $registration->confirm();

        return back()->with('flash', $registration->name.' marked as confirmed.');
    }

    public function export(): StreamedResponse
    {
        $rows = Registration::orderBy('id')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // Excel reads a UTF-8 CSV as Latin-1 without this, and every
            // Romanian name comes out mangled.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Name', 'Email', 'Organisation', 'Country', 'Phone', 'Days', 'Workshop interest', 'Language', 'Confirmed', 'Registered']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->name, $r->email, $r->organisation, $r->country, $r->phone,
                    collect($r->days)->map(fn ($d) => self::DAYS[$d] ?? $d)->implode(' '),
                    $r->workshop_interest ? 'yes' : '',
                    $r->locale,
                    $r->confirmed_at?->toDateTimeString() ?? '',
                    $r->created_at->toDateTimeString(),
                ]);
            }

            fclose($out);
        }, 'wcm2026-registrations-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /* ------------------------------------------------------------ bookings */

    public function bookings(): Response
    {
        return Inertia::render('Admin/2026/Bookings', [
            'sessions' => Session::with(['translations', 'day.translations', 'bookings'])
                ->where('bookable', true)
                ->get()
                ->map(fn (Session $s) => [
                    'id' => $s->id,
                    'title' => $s->translate('en')?->title ?? '',
                    'day' => $s->day?->date->format('D d M'),
                    'time' => substr($s->starts_at, 0, 5),
                    'capacity' => $s->capacity,
                    'taken' => $s->taken(),
                    'slug' => $s->slug,
                    'published' => $s->published,
                    'bookings' => $s->bookings->sortBy('id')->values()->map(fn (SessionBooking $b) => [
                        'id' => $b->id,
                        'name' => $b->name,
                        'email' => $b->email,
                        'phone' => $b->phone,
                        'locale' => $b->locale,
                        'cancelled' => $b->isCancelled(),
                        'created' => $b->created_at->toDateTimeString(),
                    ]),
                ]),
            'publicBase' => Front2026Controller::BASE,
        ]);
    }

    /** Frees the place without losing the evidence that it was wanted. */
    public function cancelBooking(SessionBooking $booking): RedirectResponse
    {
        $booking->cancelled_at = $booking->isCancelled() ? null : now();
        $booking->save();

        return back()->with('flash', $booking->isCancelled()
            ? $booking->name.'’s place released.'
            : $booking->name.'’s place restored.');
    }
}
