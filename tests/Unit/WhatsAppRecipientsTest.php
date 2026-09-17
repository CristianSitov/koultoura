<?php

namespace Tests\Unit;

use App\Support\WhatsApp;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WhatsAppRecipientsTest extends TestCase
{
    private function withEnv(string $value): array
    {
        Config::set('services.whatsapp.callmebot', $value);

        return WhatsApp::recipients();
    }

    public function test_parses_pairs_and_trims_spaces(): void
    {
        $this->assertSame(
            [
                ['phone' => '40712345678', 'apikey' => '123456'],
                ['phone' => '40787654321', 'apikey' => '654321'],
            ],
            $this->withEnv(' 40712345678:123456 , 40787654321:654321 ')
        );
    }

    public function test_drops_a_pair_missing_its_apikey(): void
    {
        $this->assertSame(
            [['phone' => '40712345678', 'apikey' => '123456']],
            $this->withEnv('40712345678:123456,40787654321')
        );
    }

    public function test_empty_is_no_recipients_and_not_configured(): void
    {
        $this->assertSame([], $this->withEnv(''));
        $this->assertSame([], $this->withEnv('   '));
        Config::set('services.whatsapp.callmebot', '');
        $this->assertFalse(WhatsApp::configured());
    }

    public function test_configured_when_a_recipient_is_present(): void
    {
        Config::set('services.whatsapp.callmebot', '40712345678:123456');
        $this->assertTrue(WhatsApp::configured());
    }
}
