<?php

namespace App\Console\Commands;

use App\Models\Contribution;
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
 */
class ReconcileContributions extends Command
{
    protected $signature = 'contributions:reconcile
                            {--days=90 : How far back to ask Stripe}
                            {--dry-run : Report what is missing without writing it}';

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
        $seen = 0;
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

        $this->line(sprintf('%d paid session(s) at Stripe, %d already recorded.', $seen, $seen - count($missing)));

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
        }

        if ($missing === []) {
            $this->info('Nothing missing.');
        }

        return self::SUCCESS;
    }
}
