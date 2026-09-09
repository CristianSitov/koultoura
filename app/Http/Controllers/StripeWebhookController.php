<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Support\Slack;
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

            Slack::throttled('stripe-no-secret', 'Stripe webhook has no signing secret', [
                'Effect' => 'Payments are being taken and none of them are being recorded.',
            ]);

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
            /*
             * Anyone can post here; an unsigned request is simply refused, and
             * usually it is a scanner. It is also exactly what a wrong signing
             * secret looks like, though — in which case every real payment is
             * landing here and being turned away — so it is worth one notice
             * rather than none, throttled so a scanner cannot flood the channel.
             */
            Slack::throttled('stripe-bad-signature', 'Stripe webhook refused: bad signature', [
                'From' => $request->ip(),
                'Note' => 'A scanner, or the signing secret is wrong. If payments are coming in, it is the secret.',
            ]);

            return response('', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            Contribution::recordSession($session);

            Slack::good('Contribution received', [
                'Amount' => number_format(($session->amount_total ?? 0) / 100, 2).' '.strtoupper($session->currency ?? ''),
                'From' => $session->customer_details->email ?? '(no email)',
                'Registration' => $session->client_reference_id ? 'linked' : 'NOT LINKED — not from the form?',
            ]);
        }

        if ($event->type === 'charge.refunded') {
            $charge = $event->data->object;
            $contribution = Contribution::recordRefund($charge);
            $full = ($charge->amount_refunded ?? 0) >= ($charge->amount ?? 0);

            Slack::warn($full ? 'Contribution refunded' : 'Contribution partly refunded', [
                'Amount' => number_format(($charge->amount_refunded ?? 0) / 100, 2).' '.strtoupper($charge->currency ?? ''),
                'Of' => number_format(($charge->amount ?? 0) / 100, 2).' '.strtoupper($charge->currency ?? ''),
                'To' => $charge->billing_details->email ?? $charge->receipt_email ?? '(no email)',
                'Our record' => $contribution
                    ? ($full ? 'marked refunded, out of the totals' : 'left as paid — the total still counts the full amount')
                    : 'no matching contribution — a payment from somewhere else',
            ]);
        }

        // Anything else is acknowledged so Stripe stops retrying it.
        return response('', 200);
    }
}
