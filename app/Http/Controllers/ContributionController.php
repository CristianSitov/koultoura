<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/*
 * Sends a donor to Stripe.
 *
 * A checkout session rather than a bare payment link, so the page opens in the
 * language they registered in, one product serves both languages, and "back"
 * returns here instead of dead-ending on Stripe.
 *
 * Falls back to the hosted payment link when the API secret or price is not
 * configured, so the contribute button never breaks — it just loses the
 * language handling.
 *
 * Nothing here records anything. What was actually paid arrives on the
 * webhook; a browser returning from Stripe is not evidence.
 */
class ContributionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $registration = Registration::where('token', $request->route('token'))->firstOrFail();

        $secret = config('services.stripe.secret');
        $price = config('services.stripe.price_id');

        if (! $secret || ! $price) {
            return redirect($this->paymentLink($registration) ?? $this->returnUrl($registration));
        }

        try {
            $session = (new StripeClient($secret))->checkout->sessions->create([
                'mode' => 'payment',
                'line_items' => [['price' => $price, 'quantity' => 1]],
                // How the webhook finds its way back to a person.
                'client_reference_id' => $registration->token,
                'customer_email' => $registration->email,
                'locale' => $registration->locale,
                'success_url' => $this->returnUrl($registration).'?paid=1',
                'cancel_url' => $this->returnUrl($registration),
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe checkout session failed', ['error' => $e->getMessage()]);

            // Rather than an error page: the payment link still works, and a
            // donation nobody can make is worse than one that looks plainer.
            return redirect($this->paymentLink($registration) ?? $this->returnUrl($registration));
        }

        return redirect()->away($session->url);
    }

    private function returnUrl(Registration $registration): string
    {
        $prefix = $registration->locale === 'ro' ? '/ro' : '';

        return url(Front2026Controller::BASE.$prefix.'/registered');
    }

    private function paymentLink(Registration $registration): ?string
    {
        $link = $registration->locale === 'ro'
            ? config('services.stripe.payment_link_ro') ?: config('services.stripe.payment_link')
            : config('services.stripe.payment_link');

        if (! $link) {
            return null;
        }

        return $link.'?'.http_build_query([
            'client_reference_id' => $registration->token,
            'prefilled_email' => $registration->email,
        ]);
    }
}
