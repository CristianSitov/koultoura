<?php

namespace App\Console\Commands;

use App\Support\Slack;
use Illuminate\Console\Command;

/** Proves the webhook URL works, without waiting for something to happen. */
class SlackTest extends Command
{
    protected $signature = 'slack:test';

    protected $description = 'Send a test notice to the Slack channel';

    public function handle(): int
    {
        if (blank(config('services.slack.webhook'))) {
            $this->error('SLACK_WEBHOOK_URL is not set — nothing would be sent.');

            return self::FAILURE;
        }

        Slack::info('Test notice', [
            'From' => config('app.url'),
            'Environment' => app()->environment(),
            'Meaning' => 'The webhook works. Registrations, payments and failures will arrive here.',
        ]);

        // Console commands terminate the app, which is what actually sends it.
        $this->info('Sent. Check the channel.');

        return self::SUCCESS;
    }
}
