<?php

namespace App\Console\Commands;

use App\Models\Contribution;
use App\Models\Registration;
use App\Support\Slack;
use Illuminate\Console\Command;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/*
 * Stripe is the record of what was paid; this table is our copy of it. The
 * webhook keeps the copy current, but a webhook can be missed — the endpoint
 * was down, the signing secret was wrong, the event was never delivered — and
 * a missed one is silent.
 *
 * So: ask Stripe what it has, write in anything we are missing. Keyed on the
 * session id, so running this twice changes nothing.
 *
 * Only this event's payments, though. The Stripe account is the association's,
 * not the symposium's, and it carries donations from other campaigns that have
 * nothing to do with a registration here. A session is ours when its
 * client_reference_id is a registration token — that is what the contribution
 * step puts there, and nothing else on the account sets it. Adopting the rest
 * once overstated this event's income by 470 RON.
 */
class ReconcileContributions extends Command
{
    protected $signature = 'contributions:reconcile
                            {--days=90 : How far back to ask Stripe}
                            {--dry-run : Report what is missing without writing it}
                            {--all : Every paid session on the account, not just this event\'s}';

    protected $description = 'Check every paid Stripe checkout session against the contributions table';

    public function handle(): int
    {
        $secret = config('services.stripe.secret');

        if (! $secret) {
            $this->error('No Stripe secret key configured.');

            return self::FAILURE;
        }

        $stripe = new StripeClient($secret);
        $since = now()->subDays((int) $this->option('days'))->getTimestamp();
        $ours = $this->option('all') ? null : Registration::pluck('token')->flip();
        $seen = 0;
        $skipped = 0;
        $missing = [];

        try {
            // autoPagingIterator: more than 100 sessions is entirely normal
            // once this is live, and a silent first page would defeat the point.
            $sessions = $stripe->checkout->sessions->all([
                'limit' => 100,
                'created' => ['gte' => $since],
            ])->autoPagingIterator();

            foreach ($sessions as $session) {
                if ($session->payment_status !== 'paid') {
                    continue;
                }

                // Somebody else's campaign, on the same account.
                if ($ours !== null && ! $ours->has((string) $session->client_reference_id)) {
                    $skipped++;

                    continue;
                }

                $seen++;

                if (Contribution::where('session_id', $session->id)->exists()) {
                    continue;
                }

                $missing[] = $session;
            }
        } catch (ApiErrorException $e) {
            $this->error('Stripe: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->line(sprintf('%d paid session(s) for this event, %d already recorded.', $seen, $seen - count($missing)));

        if ($skipped > 0) {
            $this->line(sprintf(
                '%d paid session(s) on the account belong to something else and were left alone (--all to include them).',
                $skipped
            ));
        }

        foreach ($missing as $session) {
            $line = sprintf(
                '  %s  %s %s  %s',
                $session->id,
                number_format($session->amount_total / 100, 2),
                strtoupper($session->currency),
                $session->customer_details->email ?? '(no email)'
            );

            if ($this->option('dry-run')) {
                $this->warn('missing '.$line);

                continue;
            }

            Contribution::recordSession($session);
            $this->info('recorded'.$line);

            /*
             * A payment the webhook should have recorded and did not. Worth
             * saying so: it means an event was missed, and the next one may be
             * missed too.
             */
            Slack::warn('Payment recorded late — the webhook missed it', [
                'Amount' => number_format($session->amount_total / 100, 2).' '.strtoupper($session->currency),
                'From' => $session->customer_details->email ?? '(no email)',
                'Session' => $session->id,
            ]);
        }

        if ($missing === []) {
            $this->info('Nothing missing.');
        }

        return self::SUCCESS;
    }
}
