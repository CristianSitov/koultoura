<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
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
            Contribution::recordSession($event->data->object);
        }

        // Anything else is acknowledged so Stripe stops retrying it.
        return response('', 200);
    }
}
