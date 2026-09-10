<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationConfirmation;
use App\Mail\RegistrationConfirmed;
use App\Models\Contribution;
use App\Models\ProgrammeDay;
use App\Models\Registration;
use App\Support\Slack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/*
 * Registration, phase one: form, confirmation email, confirm link. No money
 * yet — the contribution step slots in between `store` and the submitted page
 * once the donation-or-fee question is settled, and nothing here depends on
 * how that lands.
 *
 * The confirmation email goes out in `store`, before any payment step, so a
 * contribution is never taken against an address nobody has verified.
 */
class Front2026RegistrationController extends Controller
{
    /*
     * 7–9 October. The 10th is the Heritage School's workshop day: those have
     * their own forms and their own capacity, so registering for the symposium
     * does not cover them.
     */
    private const DAYS = [1, 2, 3];

    /** The submitted page for this registration, in its own language. */
    private function submittedUrl(Registration $registration): string
    {
        $prefix = $registration->locale === 'ro' ? '/ro' : '';

        return Front2026Controller::base().$prefix.'/registered/'.$registration->token;
    }

    public function create(): Response
    {
        return Inertia::render('2026/Registration', [
            'base' => Front2026Controller::base(),
            'days' => self::DAYS,
            'themes' => $this->dayThemes(),
        ]);
    }

    /*
     * What each day is about, so the checkbox says what you are choosing
     * rather than only which date it falls on. Read straight from the
     * programme, keyed by the same day numbers the form posts, and left out
     * for any day that has no theme set yet — a checkbox with a blank line
     * under it is worse than one without.
     */
    private function dayThemes(): array
    {
        $locale = app()->getLocale() === 'ro' ? 'ro' : 'en';

        return ProgrammeDay::with('theme.translations')
            ->orderBy('position')
            ->orderBy('date')
            ->get()
            ->values()
            ->mapWithKeys(function (ProgrammeDay $day, int $i) use ($locale) {
                $title = $day->theme?->translate($locale)?->title
                    ?: $day->theme?->translate('en')?->title;

                return [$i + 1 => $title ? $day->theme->numeral.' · '.$title : null];
            })
            ->filter()
            ->all();
    }

    public function store(Request $request): RedirectResponse
    {
        // A bot fills every field it is given; a person never sees this one.
        // Sent to the landing page rather than a registration page it has no
        // registration for.
        if (filled($request->input('website'))) {
            return redirect(Front2026Controller::base());
        }

        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // rfc only, not dns: the confirmation link is what proves the
            // address, and an MX lookup both adds a network round trip to every
            // submission and rejects valid domains that receive mail without one.
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'email_confirmation' => ['required', 'same:email'],
            'organisation' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9\s\-\+\(\)]+$/'],
            'days' => ['required', 'array', 'min:1'],
            'days.*' => [Rule::in(self::DAYS)],
            'consent' => ['accepted'],
        ]);

        $registration = Registration::firstOrNew(['email' => $input['email']]);

        /*
         * An address that has already registered keeps the days it chose. The
         * common case is someone who never received the email and fills the
         * form in again — they are shown what they booked and offered the link
         * again, rather than silently overwriting a reservation the office may
         * already have counted.
         */
        if ($registration->exists) {
            return redirect($this->submittedUrl($registration));
        }

        $registration->fill([
            'name' => $input['name'],
            'organisation' => $input['organisation'] ?? null,
            'country' => $input['country'] ?? null,
            'phone' => $input['phone'] ?? null,
            // Cast: a form post gives strings, an Inertia post gives numbers,
            // and the stored JSON should not depend on which.
            'days' => array_map('intval', $input['days']),
            'locale' => app()->getLocale(),
            'consented_at' => now(),
        ])->save();

        Slack::good('New registration', [
            'Name' => $registration->name,
            'Email' => $registration->email,
            'Days' => self::dayList($registration),
            'Organisation' => $registration->organisation,
            'Country' => $registration->country,
            'Language' => strtoupper($registration->locale),
        ]);

        /*
         * Sent here, not at the end of the flow.
         *
         * It used to wait for the "check your email" page, so that one message
         * arrived after the whole thing rather than mid-way through it. That
         * page is past the contribution step, and on 10 September two of the
         * day's four registrations never reached it: both arrived from the
         * Instagram bio link, both stopped on the page that asks for a card,
         * and neither was ever emailed. Nothing failed — nothing was sent.
         *
         * Confirming an address has nothing to do with donating, so it no
         * longer waits behind it.
         */
        $this->sendConfirmation($registration);

        return redirect(Front2026Controller::base().'/contribute/'.$registration->token);
    }

    public function submitted(Request $request): Response
    {
        $registration = Registration::where('token', $request->route('token'))->firstOrFail();

        /*
         * The second chance. `store` has already sent this; the only way to
         * arrive here unsent is a mailer that was down a moment ago, and this
         * is the next request that can try again. A reload is not a reason to
         * send another, and neither is an address already confirmed.
         */
        if ($registration->sent_count === 0 && ! $registration->isConfirmed()) {
            $this->sendConfirmation($registration);
        }

        return Inertia::render('2026/RegistrationSubmitted', [
            'base' => Front2026Controller::base(),
            'email' => $registration->email,
            // Re-read: the send above may have just changed it.
            'sent' => $registration->fresh()->sent_count > 0,
            // Shown, not editable: what this address is down for.
            'days' => $registration->days,
            // Our own route, which decides how to reach Stripe. Offered
            // whether or not the address is confirmed: contributing is a
            // separate thing from confirming, and someone who skipped it on
            // the way through is exactly who this is for.
            'contributeUrl' => Front2026Controller::base().'/contribute/'.$registration->token,
            'confirmed' => $registration->isConfirmed(),
            'paid' => $request->boolean('paid'),
            'contribution' => $request->boolean('paid')
                ? $this->contribution($registration, $request->query('session_id'))
                : null,
        ]);
    }


    public function confirm(Request $request): Response|RedirectResponse
    {
        // By name, not position: on /{locale}/confirm/{token} a positional
        // argument picks up the locale instead of the token.
        $registration = Registration::where('token', $request->route('token'))->firstOrFail();

        /*
         * Inertia computes its shared props — the locale the page renders in
         * among them — in middleware, before this runs, so setting the locale
         * here would be a request too late. Bounce to the address that carries
         * the language instead; the emailed link already points there, so this
         * only fires for a link made before that.
         */
        if ($request->route('locale') === null && $registration->locale !== 'en') {
            return redirect($registration->confirmUrl());
        }

        $alreadyConfirmed = $registration->isConfirmed();
        $registration->confirm();

        /*
         * The second email: the dates in writing, the way into the programme,
         * and the event as a calendar entry. Only on the visit that actually
         * confirms — clicking the link again is not a reason to send it twice.
         */
        if (! $alreadyConfirmed) {
            $this->sendConfirmed($registration);

            Slack::info('Address confirmed', [
                'Name' => $registration->name,
                'Email' => $registration->email,
                'Days' => self::dayList($registration),
            ]);
        }

        return Inertia::render('2026/RegistrationConfirmed', [
            'base' => Front2026Controller::base(),
            'name' => $registration->name,
            'alreadyConfirmed' => $alreadyConfirmed,
        ]);
    }

    /*
     * Not named `resend`: the Resend mail SDK puts a class of that name in the
     * global namespace, and Laravel's group-controller resolution does a
     * case-insensitive class_exists() on a bare action string — so
     * Route::post('/resend', 'resend') resolves to the SDK class and dies with
     * "Invalid route action".
     */
    public function resendConfirmation(Request $request): RedirectResponse
    {
        $email = $request->validate(['email' => ['required', 'email', 'max:255']])['email'];
        $registration = Registration::where('email', $email)->first();

        if ($registration && ! $registration->isConfirmed() && $registration->canResend()) {
            $this->sendConfirmation($registration);
        }

        // Always the same answer: whether an address is registered is not
        // something a stranger gets to probe for.
        return back();
    }

    /**
     * What they just gave, for the thank-you.
     *
     * The webhook is what records a contribution, but it may not have landed
     * by the time Stripe sends them back here — so read our own row first and
     * ask Stripe only if it has not arrived yet. Either way this is for
     * display; the row the webhook writes remains the record.
     *
     * @return array{amount: int, currency: string}|null
     */
    private function contribution(Registration $registration, ?string $sessionId): ?array
    {
        $row = Contribution::where('registration_id', $registration->id)
            ->when($sessionId, fn ($query) => $query->orWhere('session_id', $sessionId))
            ->latest('id')
            ->first();

        if ($row) {
            return ['amount' => $row->amount, 'currency' => strtoupper($row->currency)];
        }

        if (! $sessionId || ! config('services.stripe.secret')) {
            return null;
        }

        try {
            $session = (new StripeClient(config('services.stripe.secret')))
                ->checkout->sessions->retrieve($sessionId);
        } catch (ApiErrorException $e) {
            Log::warning('Could not read the checkout session for the thank-you', ['error' => $e->getMessage()]);

            return null;
        }

        return $session->payment_status === 'paid'
            ? ['amount' => (int) $session->amount_total, 'currency' => strtoupper($session->currency)]
            : null;
    }

    /**
     * Sends the confirmation, and does not take the registration down with it
     * if the mailer is having a bad day.
     *
     * A rejected API key used to surface as a 500 on the form — the row was
     * already saved, so the person was registered and told they had failed.
     * The send is logged and the page carries a "send it again" button, which
     * is the second chance this needs.
     */
    /** The welcome that follows confirming; a failure here is not the visitor's problem. */
    private function sendConfirmed(Registration $registration): void
    {
        try {
            Mail::to($registration->email)->send(new RegistrationConfirmed($registration));
        } catch (Throwable $e) {
            Log::error('2026 confirmed email failed', [
                'registration' => $registration->id,
                'error' => $e->getMessage(),
            ]);

            Slack::alert('Registration details email did NOT go out', [
                'Email' => $registration->email,
                'Registration' => '#'.$registration->id,
                'Error' => $e->getMessage(),
            ]);
        }
    }

    /** "7, 8 & 9 October", for a line in a Slack message. */
    private static function dayList(Registration $registration): string
    {
        $dates = [1 => '7', 2 => '8', 3 => '9', 4 => '10'];
        $days = array_values(array_filter(array_map(
            fn ($d) => $dates[$d] ?? null,
            $registration->days ?? []
        )));

        sort($days);

        return $days === [] ? '—' : implode(', ', $days).' October';
    }

    private function sendConfirmation(Registration $registration): bool
    {
        try {
            Mail::to($registration->email)->send(
                new RegistrationConfirmation($registration, $registration->confirmUrl())
            );
        } catch (Throwable $e) {
            Log::error('2026 confirmation email failed', [
                'registration' => $registration->id,
                'error' => $e->getMessage(),
            ]);

            /*
             * The registration is saved either way, so this failure is silent
             * to everyone except this notice: the person is waiting for an
             * email that is not coming, and only the office can rescue them.
             */
            Slack::alert('Confirmation email did NOT go out', [
                'Email' => $registration->email,
                'Registration' => '#'.$registration->id,
                'Error' => $e->getMessage(),
                'Fix' => 'Resend from /dashboard/registrations',
            ]);

            return false;
        }

        $registration->markSent();

        return true;
    }
}
