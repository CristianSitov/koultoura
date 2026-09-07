<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

/*
 * The only thing that may record a contribution.
 *
 * Payment happens on a Stripe-hosted payment link, so nothing else in this app
 * talks to Stripe — no API key, no session creation. This endpoint exists to
 * hear that a payment completed, and it trusts the request only because the
 * signature checks out.
 *
 * Deliberately outside the landing page's secret path: that segment exists to
 * keep an unlisted page unfound and may be rotated, and a webhook URL Stripe
 * holds should not move when it is.
 */
class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');

        if (! $secret) {
            Log::warning('Stripe webhook received but no signing secret is configured.');

            return response('', 500);
        }

        try {
            // Raw body, not the parsed input: the signature covers the exact bytes.
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $secret
            );
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            // Anyone can post here; an unsigned request is simply refused.
            return response('', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $this->record($event->data->object);
        }

        // Anything else is acknowledged so Stripe stops retrying it.
        return response('', 200);
    }

    private function record(object $session): void
    {
        if (($session->payment_status ?? null) !== 'paid') {
            return;
        }

        $registration = filled($session->client_reference_id ?? null)
            ? Registration::where('token', $session->client_reference_id)->first()
            : null;

        // Keyed on the session id: Stripe retries, and a retry must not double
        // count the same donation.
        Contribution::updateOrCreate(
            ['session_id' => $session->id],
            [
                'registration_id' => $registration?->id,
                'payment_intent_id' => $session->payment_intent ?? null,
                'email' => $session->customer_details->email ?? null,
                'amount' => (int) ($session->amount_total ?? 0),
                'currency' => strtolower($session->currency ?? 'ron'),
                'status' => $session->payment_status,
                'paid_at' => now(),
            ]
        );
    }
}
