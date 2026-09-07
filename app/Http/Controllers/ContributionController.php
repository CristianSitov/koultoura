<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/*
 * The contribution step: a page of our own between submitting the form and
 * being told to check your email.
 *
 * Stripe's form is embedded here rather than redirected to, so nobody is
 * handed off to another domain halfway through registering. Card details go
 * straight from that iframe to Stripe — they never touch this server.
 *
 * Three ways this degrades, in order:
 *   - no publishable key   → a button that redirects to hosted checkout
 *   - no secret or price   → a button that opens the hosted payment link
 *   - none of the above    → the page is skipped entirely
 *
 * Nothing here records anything. What was actually paid arrives on the
 * webhook; a browser returning from Stripe is not evidence.
 */
class ContributionController extends Controller
{
    /** The step itself. */
    public function show(Request $request): Response|RedirectResponse
    {
        $registration = Registration::where('token', $request->route('token'))->firstOrFail();

        if (! $this->configured()) {
            return redirect($this->submittedUrl($registration));
        }

        return Inertia::render('2026/Contribute', [
            'base' => Front2026Controller::BASE,
            'skipUrl' => $this->submittedUrl($registration),
            'publishableKey' => config('services.stripe.key'),
            // Present only when the form can be embedded; otherwise the page
            // offers the redirect below instead.
            'clientSecret' => config('services.stripe.key') ? $this->embeddedSecret($registration) : null,
            'redirectUrl' => Front2026Controller::BASE.'/contribute/'.$registration->token.'/redirect',
        ]);
    }

    /** The way out to Stripe's own page, for when embedding is not available. */
    public function redirectToStripe(Request $request): RedirectResponse
    {
        $registration = Registration::where('token', $request->route('token'))->firstOrFail();

        try {
            $session = $this->stripe()?->checkout->sessions->create([
                'mode' => 'payment',
                'line_items' => [['price' => config('services.stripe.price_id'), 'quantity' => 1]],
                'client_reference_id' => $registration->token,
                'customer_email' => $registration->email,
                'locale' => $registration->locale,
                'success_url' => url($this->submittedUrl($registration)).'?paid=1',
                'cancel_url' => url(Front2026Controller::BASE.'/contribute/'.$registration->token),
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe checkout session failed', ['error' => $e->getMessage()]);
            $session = null;
        }

        return redirect()->away($session?->url ?? $this->paymentLink($registration) ?? url($this->submittedUrl($registration)));
    }

    private function embeddedSecret(Registration $registration): ?string
    {
        try {
            $session = $this->stripe()?->checkout->sessions->create([
                'ui_mode' => 'embedded',
                'mode' => 'payment',
                'line_items' => [['price' => config('services.stripe.price_id'), 'quantity' => 1]],
                'client_reference_id' => $registration->token,
                'customer_email' => $registration->email,
                'locale' => $registration->locale,
                'return_url' => url($this->submittedUrl($registration)).'?paid=1',
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe embedded session failed', ['error' => $e->getMessage()]);

            return null;
        }

        return $session?->client_secret;
    }

    private function stripe(): ?StripeClient
    {
        $secret = config('services.stripe.secret');

        return $secret ? new StripeClient($secret) : null;
    }

    private function configured(): bool
    {
        return (bool) (config('services.stripe.secret') && config('services.stripe.price_id'))
            || (bool) config('services.stripe.payment_link');
    }

    private function submittedUrl(Registration $registration): string
    {
        $prefix = $registration->locale === 'ro' ? '/ro' : '';

        return Front2026Controller::BASE.$prefix.'/registered/'.$registration->token;
    }

    private function paymentLink(Registration $registration): ?string
    {
        $link = $registration->locale === 'ro'
            ? config('services.stripe.payment_link_ro') ?: config('services.stripe.payment_link')
            : config('services.stripe.payment_link');

        return $link ? $link.'?'.http_build_query([
            'client_reference_id' => $registration->token,
            'prefilled_email' => $registration->email,
        ]) : null;
    }
}
