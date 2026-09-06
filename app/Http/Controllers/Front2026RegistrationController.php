<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationConfirmation;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
    private const DAYS = [1, 2, 3, 4];

    /** The submitted page in whatever language the form was in. */
    private function submittedUrl(): string
    {
        $prefix = app()->getLocale() === 'ro' ? '/ro' : '';

        return Front2026Controller::BASE.$prefix.'/registered';
    }

    public function create(): Response
    {
        return Inertia::render('2026/Registration', [
            'base' => Front2026Controller::BASE,
            'days' => self::DAYS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // A bot fills every field it is given; a person never sees this one.
        if (filled($request->input('website'))) {
            return redirect($this->submittedUrl());
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
            'workshop_interest' => ['boolean'],
            'consent' => ['accepted'],
        ]);

        $registration = Registration::firstOrNew(['email' => $input['email']]);

        // Registering again with the same address updates the details and
        // re-sends the link rather than erroring — the common case is someone
        // who never received the first one.
        if ($registration->exists && ! $registration->canResend()) {
            return redirect($this->submittedUrl())
                ->with('registration_email', $registration->email);
        }

        $registration->fill([
            'name' => $input['name'],
            'organisation' => $input['organisation'] ?? null,
            'country' => $input['country'] ?? null,
            'phone' => $input['phone'] ?? null,
            // Cast: a form post gives strings, an Inertia post gives numbers,
            // and the stored JSON should not depend on which.
            'days' => array_map('intval', $input['days']),
            'workshop_interest' => (bool) ($input['workshop_interest'] ?? false),
            'locale' => app()->getLocale(),
            'consented_at' => now(),
        ])->save();

        $this->sendConfirmation($registration);

        return redirect($this->submittedUrl())
            ->with('registration_email', $registration->email);
    }

    public function submitted(Request $request): Response
    {
        return Inertia::render('2026/RegistrationSubmitted', [
            'base' => Front2026Controller::BASE,
            'email' => $request->session()->get('registration_email'),
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
            return redirect($this->confirmUrl($registration));
        }

        $alreadyConfirmed = $registration->isConfirmed();
        $registration->confirm();

        return Inertia::render('2026/RegistrationConfirmed', [
            'base' => Front2026Controller::BASE,
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
        return back()->with('registration_email', $email);
    }

    private function confirmUrl(Registration $registration): string
    {
        $prefix = $registration->locale === 'ro' ? '/ro' : '';

        return Front2026Controller::BASE.$prefix.'/confirm/'.$registration->token;
    }

    private function sendConfirmation(Registration $registration): void
    {
        $url = url($this->confirmUrl($registration));

        Mail::to($registration->email)->send(new RegistrationConfirmation($registration, $url));

        $registration->markSent();
    }
}
