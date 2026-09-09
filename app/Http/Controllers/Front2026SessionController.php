<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Session;
use App\Models\SessionBooking;
use App\Support\Slack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/*
 * A place in a capped session.
 *
 * Separate from the day registration on purpose: those two things promise
 * different amounts. Registering says which days you are coming; this says a
 * particular workshop is holding a seat for you, and there are only so many.
 *
 * The count is taken under a lock, because two people pressing the button at
 * the same moment is exactly how a room ends up with one chair too few.
 */
class Front2026SessionController extends Controller
{
    public function show(Request $request): Response
    {
        $session = $this->session($request);

        return Inertia::render('2026/SessionBooking', [
            'base' => Front2026Controller::base(),
            'session' => $this->payload($session),
            'booked' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $session = $this->session($request);

        // A bot fills every field it is given; a person never sees this one.
        if (filled($request->input('website'))) {
            return redirect(Front2026Controller::base());
        }

        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9\s\-\+\(\)]+$/'],
            'consent' => ['accepted'],
        ]);

        $outcome = DB::connection('wcm_2026')->transaction(function () use ($session, $input) {
            $existing = SessionBooking::where('session_id', $session->id)
                ->where('email', $input['email'])
                ->lockForUpdate()
                ->first();

            // Booking twice from the same address is the same place, not two.
            if ($existing && ! $existing->isCancelled()) {
                return ['status' => 'already', 'booking' => $existing];
            }

            $taken = SessionBooking::where('session_id', $session->id)
                ->whereNull('cancelled_at')
                ->lockForUpdate()
                ->count();

            if ($session->capacity !== null && $taken >= $session->capacity) {
                return ['status' => 'full', 'booking' => null];
            }

            if ($existing) {
                $existing->fill(['cancelled_at' => null] + $input)->save();

                return ['status' => 'booked', 'booking' => $existing];
            }

            return ['status' => 'booked', 'booking' => SessionBooking::create($input + [
                'session_id' => $session->id,
                'locale' => app()->getLocale(),
                // Linked when the address is already registered for the days,
                // so the office can see both halves of one person.
                'registration_id' => Registration::where('email', $input['email'])->value('id'),
            ])];
        });

        if ($outcome['status'] === 'full') {
            return back()->withErrors(['name' => __('The last place went while you were filling this in.')]);
        }

        if ($outcome['status'] === 'booked') {
            $left = $session->fresh()->places_left;

            Slack::good('Workshop place booked', [
                'Workshop' => $session->translate('en')?->title ?? $session->slug,
                'Name' => $outcome['booking']->name,
                'Email' => $outcome['booking']->email,
                'Places left' => $left === null ? 'uncapped' : (string) $left,
            ]);

            // The last place is worth knowing about the moment it goes.
            if ($left === 0) {
                Slack::warn('Workshop is now full', [
                    'Workshop' => $session->translate('en')?->title ?? $session->slug,
                ]);
            }
        }

        return redirect($this->url($session, $outcome['booking']->token));
    }

    /** The page after booking, which survives a reload. */
    public function booked(Request $request): Response
    {
        $session = $this->session($request);
        $booking = SessionBooking::where('token', $request->route('token'))->firstOrFail();

        return Inertia::render('2026/SessionBooking', [
            'base' => Front2026Controller::base(),
            'session' => $this->payload($session),
            'booked' => ['name' => $booking->name, 'cancelled' => $booking->isCancelled()],
        ]);
    }

    private function url(Session $session, string $token): string
    {
        $prefix = app()->getLocale() === 'ro' ? '/ro' : '';

        return Front2026Controller::base().$prefix.'/sessions/'.$session->slug.'/booked/'.$token;
    }

    /** Bookable and published, or it is not offered at all. */
    private function session(Request $request): Session
    {
        return Session::with(['translations', 'day.translations', 'speakers'])
            ->where('slug', $request->route('slug'))
            ->where('bookable', true)
            ->where('published', true)
            ->firstOrFail();
    }

    private function payload(Session $session): array
    {
        $text = $session->translate(app()->getLocale()) ?? $session->translate('en');

        return [
            'title' => $text->title ?? '',
            'subtitle' => $text->subtitle ?? '',
            'description' => $text->description ?? '',
            'audience' => $text->audience ?? '',
            'kind' => $session->kind,
            'time' => substr($session->starts_at, 0, 5),
            'date' => $session->day?->date->format('j F Y'),
            'speakers' => $session->speakers->pluck('full_name')->implode(', '),
            'placesLeft' => $session->places_left,
            'full' => $session->isFull(),
            // Where the form posts, in the language being read.
            'bookUrl' => Front2026Controller::base()
                .(app()->getLocale() === 'ro' ? '/ro' : '')
                .'/sessions/'.$session->slug,
        ];
    }
}
