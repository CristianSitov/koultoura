<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front2026Controller;
use App\Mail\RegistrationConfirmation;
use App\Mail\RegistrationConfirmed;
use App\Models\Contribution;
use App\Models\Registration;
use App\Models\Session;
use App\Models\Person;
use App\Models\SessionBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Support\SessionImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
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
    /*
     * All four days, because a registration made before the 10th became the
     * workshop day may still carry it and has to render.
     */
    private const DAYS = [1 => '07', 2 => '08', 3 => '09', 4 => '10'];

    /** The three the form offers; the 10th is booked through its workshops. */
    private const REGISTRATION_DAYS = [1, 2, 3];

    public function index(Request $request): Response
    {
        $day = $request->integer('day') ?: null;
        $status = $request->string('status')->toString();

        $registrations = Registration::query()
            ->when($day, fn ($q) => $q->whereJsonContains('days', $day))
            ->when($status === 'confirmed', fn ($q) => $q->whereNotNull('confirmed_at'))
            ->when($status === 'waiting', fn ($q) => $q->whereNull('confirmed_at')->where('sent_count', '>', 0))
            // The state worth being able to list: registered, never written to.
            ->when($status === 'unsent', fn ($q) => $q->whereNull('confirmed_at')->where('sent_count', 0))
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
                // Said in words rather than dates: the question this page
                // answers is "how long have they been waiting", and Carbon
                // knows the server's timezone where the browser does not.
                'sent_ago' => $r->last_sent_at?->diffForHumans(),
                'confirmed_ago' => $r->confirmed_at?->diffForHumans(),
            ]),
            'filters' => ['day' => $day, 'status' => $status ?: 'all'],
            'counts' => $this->counts(),
            'publicBase' => Front2026Controller::base(),
        ]);
    }

    /** Headcounts, which is the question actually being asked of this page. */
    private function counts(): array
    {
        $confirmed = Registration::whereNotNull('confirmed_at');

        $perDay = [];

        foreach (self::REGISTRATION_DAYS as $day) {
            $perDay[$day] = [
                'date' => self::DAYS[$day],
                'all' => Registration::whereJsonContains('days', $day)->count(),
                'confirmed' => (clone $confirmed)->whereJsonContains('days', $day)->count(),
            ];
        }

        return [
            'total' => Registration::count(),
            'confirmed' => Registration::whereNotNull('confirmed_at')->count(),
            'unsent' => Registration::whereNull('confirmed_at')->where('sent_count', 0)->count(),
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

        try {
            Mail::to($registration->email)->send(
                new RegistrationConfirmation($registration, $registration->confirmUrl())
            );
        } catch (Throwable $e) {
            Log::error('2026 confirmation resend failed', [
                'registration' => $registration->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['resend' => 'The email did not go out: '.$e->getMessage()]);
        }

        $registration->forceFill([
            'last_sent_at' => now(),
            'sent_count' => $registration->sent_count + 1,
        ])->save();

        return back()->with('flash', 'Confirmation resent to '.$registration->email);
    }

    /*
     * Sends the "you are registered" email again — the one with the venue and
     * the calendar file, which the resend above does not cover: that one asks
     * for a confirmation this person has already given.
     *
     * Wanted the day FABER's address turned out to be wrong in it. Anything of
     * that kind changes what people already have in their inbox and in their
     * calendar, and there was no way to put it right from here.
     */
    public function resendConfirmed(Registration $registration): RedirectResponse
    {
        if (! $registration->isConfirmed()) {
            return back()->with('flash', 'Not confirmed yet — send the confirmation instead.');
        }

        try {
            Mail::to($registration->email)->send(new RegistrationConfirmed($registration));
        } catch (Throwable $e) {
            Log::error('2026 confirmed email resend failed', [
                'registration' => $registration->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['resend' => 'The email did not go out: '.$e->getMessage()]);
        }

        $registration->forceFill([
            'last_sent_at' => now(),
            'sent_count' => $registration->sent_count + 1,
        ])->save();

        return back()->with('flash', 'Details resent to '.$registration->email);
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
            'sessions' => Session::with(['translations', 'day.translations', 'bookings', 'speakers'])
                ->where('bookable', true)
                ->orderBy('programme_day_id')
                ->orderBy('starts_at')
                ->get()
                ->map(fn (Session $s) => [
                    'id' => $s->id,
                    'type' => $s->type,
                    'title' => $s->translate('en')?->title ?? '',
                    'day' => $s->day?->date->format('D d M'),
                    'time' => substr($s->starts_at, 0, 5),
                    'capacity' => $s->capacity,
                    'taken' => $s->taken(),
                    'slug' => $s->slug,
                    'image' => $s->image,
                    'published' => $s->published,
                    // Editable identity, both languages.
                    'en' => ['title' => $s->translate('en')?->title ?? '', 'subtitle' => $s->translate('en')?->subtitle ?? ''],
                    'ro' => ['title' => $s->translate('ro')?->title ?? '', 'subtitle' => $s->translate('ro')?->subtitle ?? ''],
                    'trainers' => $s->speakers->pluck('id')->all(),
                    'trainerNames' => $s->speakers->pluck('full_name')->implode(', '),
                    'new_person' => ['first' => '', 'last' => ''],
                    'bookings' => $s->bookings->sortBy('id')->values()->map(fn (SessionBooking $b) => [
                        'id' => $b->id,
                        'name' => $b->name,
                        'first_name' => $b->first_name,
                        'last_name' => $b->last_name,
                        'email' => $b->email,
                        'phone' => $b->phone,
                        'locale' => $b->locale,
                        'cancelled' => $b->isCancelled(),
                        'created' => $b->created_at->toDateTimeString(),
                    ]),
                ]),
            // For the trainer/guide picker — hidden people are eligible too.
            'people' => Person::orderBy('full_name')->get(['id', 'full_name', 'published'])
                ->map(fn ($p) => ['id' => $p->id, 'name' => $p->full_name, 'onGrid' => (bool) $p->published]),
            'publicBase' => Front2026Controller::base(),
        ]);
    }

    /**
     * Edit a workshop's identity from the bookings view: title, subtitle,
     * picture and who leads it. Scheduling, capacity and the rest stay in the
     * programme's own session form.
     */
    public function updateWorkshop(Request $request, Session $session): RedirectResponse
    {
        $data = $request->validate([
            'en.title' => ['required', 'string', 'max:255'],
            'en.subtitle' => ['nullable', 'string', 'max:255'],
            'ro.title' => ['nullable', 'string', 'max:255'],
            'ro.subtitle' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:8192'],
            'trainers' => ['array'],
            'trainers.*' => [Rule::exists('wcm_2026.people', 'id')],
            'new_person.first' => ['nullable', 'required_with:new_person.last', 'string', 'max:255'],
            'new_person.last' => ['nullable', 'required_with:new_person.first', 'string', 'max:255'],
        ]);

        foreach (['en', 'ro'] as $locale) {
            $t = $session->translateOrNew($locale);
            $t->title = $data[$locale]['title'] ?? '';
            $t->subtitle = $data[$locale]['subtitle'] ?? null;
        }
        $session->save();

        if ($request->hasFile('image') && filled($session->slug)) {
            $session->image = SessionImage::store(
                $session->slug,
                file_get_contents($request->file('image')->getRealPath())
            );
            $session->save();
        }

        $trainers = collect($data['trainers'] ?? []);

        if (filled($data['new_person']['first'] ?? null)) {
            $name = trim($data['new_person']['first'].' '.$data['new_person']['last']);
            $person = new Person(['full_name' => $name, 'published' => false]);
            $person->slug = Str::slug($name);
            $person->save();
            $trainers->push($person->id);
        }

        $session->speakers()->sync(
            $trainers->values()->mapWithKeys(fn ($id, $i) => [$id => ['position' => $i + 1]])->all()
        );

        return back()->with('flash', ($session->translate('en')?->title ?? 'Workshop').' updated.');
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

    /** The office books someone in by hand, or brings a cancelled seat back. */
    public function addBooking(Request $request): RedirectResponse
    {
        $data = $this->bookingData($request, null, (int) $request->input('session_id'));
        $session = Session::where('bookable', true)->findOrFail($data['session_id']);

        // Reuse the row for this address if there is one — cancelled or not —
        // so the (session, email) unique key never collides.
        $session->bookings()->updateOrCreate(
            ['email' => $data['email']],
            $data + [
                'cancelled_at' => null,
                'locale' => 'ro',
                'registration_id' => Registration::where('email', $data['email'])->value('id'),
            ]
        );

        return back()->with('flash', $data['name'].' booked.');
    }

    public function updateBooking(Request $request, SessionBooking $booking): RedirectResponse
    {
        $booking->update($this->bookingData($request, $booking, $booking->session_id));

        return back()->with('flash', $booking->name.'’s booking updated.');
    }

    /** A hard delete, for a row entered by mistake — Release is the soft one. */
    public function deleteBooking(SessionBooking $booking): RedirectResponse
    {
        $name = $booking->name;
        $booking->delete();

        return back()->with('flash', $name.'’s booking deleted.');
    }

    /** The shared, validated booking fields, with `name` composed from the two. */
    private function bookingData(Request $request, ?SessionBooking $booking, int $sessionId): array
    {
        $data = $request->validate([
            'session_id' => ['sometimes', Rule::exists('wcm_2026.sessions', 'id')],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email:rfc', 'max:255',
                // One place per address per session — ignoring this same row.
                Rule::unique('wcm_2026.session_bookings', 'email')
                    ->where('session_id', $sessionId)
                    ->ignore($booking?->id),
            ],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9\s\-\+\(\)]+$/'],
        ]);

        $data['name'] = trim($data['first_name'].' '.$data['last_name']);
        $data['session_id'] = $sessionId;

        return $data;
    }
}
