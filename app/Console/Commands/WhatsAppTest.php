<?php

namespace App\Console\Commands;

use App\Support\WhatsApp;
use Illuminate\Console\Command;

/** Proves the CallMeBot recipients work, one line to each, right now. */
class WhatsAppTest extends Command
{
    protected $signature = 'whatsapp:test';

    protected $description = 'Send a test WhatsApp notice to every configured recipient';

    public function handle(): int
    {
        if (! WhatsApp::configured()) {
            $this->error('WHATSAPP_CALLMEBOT is empty — no recipients, nothing would be sent.');
            $this->line('Set it to "phone:apikey" pairs, comma-separated. Each person gets their');
            $this->line('apikey by messaging the CallMeBot number once (see .env.example).');

            return self::FAILURE;
        }

        $results = WhatsApp::test('✅ *Test notice* · '.config('app.url').' · '.now()->toDateTimeString());

        foreach ($results as $phone => $ok) {
            $this->line(($ok ? '<info>sent</info>   ' : '<error>failed</error> ').$phone);
        }

        return in_array(false, $results, true) ? self::FAILURE : self::SUCCESS;
    }
}
