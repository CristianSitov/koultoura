<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Session;
use App\Models\SessionBooking;
use App\Support\HtmlBio;
use App\Support\Slack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

        // A workshop for young people asks the attendee's age; under 18 a parent
        // or guardian books for the child and gives a written consent.
        $phone = ['string', 'max:40', 'regex:/^[0-9\s\-\+\(\)]+$/'];
        $minor = $session->youth && (int) $request->input('age') > 0 && (int) $request->input('age') < 18;

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'consent' => ['accepted'],
        ];

        if ($session->youth) {
            $rules['age'] = ['required', 'integer', 'min:1', 'max:120'];
        }

        if ($minor) {
            // The child stays the beneficiary (the name above); the guardian's
            // name, phone and written consent are recorded against the booking.
            $request->merge(['guardian_consent' => trim((string) $request->input('guardian_consent'))]);
            $rules['guardian_name'] = ['required', 'string', 'max:255'];
            $rules['guardian_phone'] = array_merge(['required'], $phone);
            $rules['guardian_consent'] = ['required', 'string', Rule::in(['De acord'])];
            // The parent's number is the one that reaches them; the child's is not asked.
            $rules['phone'] = array_merge(['nullable'], $phone);
        } else {
            // A workshop that moves has to be able to reach people.
            $rules['phone'] = array_merge(['required'], $phone);
        }

        $input = $request->validate($rules, [
            'guardian_consent.in' => __('Please type “De acord” to give your consent.'),
        ]);

        // Kept as one line too, for everything downstream that prints a name.
        $input['name'] = trim($input['first_name'].' '.$input['last_name']);

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
        // Signed in is the preview: the office can open a draft workshop's page
        // and its form before it is public, the same way the programme shows
        // drafts only to them. Everyone else sees published ones only.
        return Session::with(['translations', 'day.translations', 'speakers'])
            ->where('slug', $request->route('slug'))
            ->where('bookable', true)
            ->when(! auth()->check(), fn ($q) => $q->where('published', true))
            ->firstOrFail();
    }

    private function payload(Session $session): array
    {
        $text = $session->translate(app()->getLocale()) ?? $session->translate('en');

        return [
            'title' => $text->title ?? '',
            'subtitle' => $text->subtitle ?? '',
            // The booking page renders this as HTML; clean it the same way
            // the modal and the bios do.
            'description' => HtmlBio::clean($text->description ?? null),
            'audience' => $text->audience ?? '',
            'kind' => $session->kind,
            'time' => substr($session->starts_at, 0, 5),
            'date' => $session->day?->date->format('j F Y'),
            // For young people: the form asks an age and, under 18, a guardian.
            'youth' => (bool) $session->youth,
            'speakers' => $session->speakers->pluck('full_name')->implode(', '),
            // The picture and who leads it, for the column beside the form.
            'image' => $session->image,
            'people' => $session->speakers->map(fn ($p) => [
                'name' => $p->full_name,
                'photo' => $p->avatar,
            ])->all(),
            'full' => $session->isFull(),
            // Where the form posts, in the language being read.
            'bookUrl' => Front2026Controller::base()
                .(app()->getLocale() === 'ro' ? '/ro' : '')
                .'/sessions/'.$session->slug,
        ];
    }
}
