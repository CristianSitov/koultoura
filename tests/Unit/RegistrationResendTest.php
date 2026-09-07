<?php

namespace Tests\Unit;

use App\Models\Registration;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/*
 * The resend throttle is the one piece of logic here that is neither obvious
 * nor exercised by clicking through the form: it is what stops the "send it
 * again" link becoming a way to mail somebody repeatedly.
 *
 * Extends the application TestCase rather than PHPUnit's: the datetime casts
 * ask the model for its connection, which needs a booted container. No
 * database is touched — nothing here is saved.
 */
class RegistrationResendTest extends TestCase
{
    private function registration(array $attributes = []): Registration
    {
        return (new Registration())->forceFill(array_merge([
            'sent_count' => 0,
            'last_sent_at' => null,
            'confirmed_at' => null,
        ], $attributes));
    }

    public function test_a_first_send_is_allowed(): void
    {
        $this->assertTrue($this->registration()->canResend());
    }

    public function test_it_refuses_a_second_send_within_two_minutes(): void
    {
        $this->assertFalse($this->registration([
            'sent_count' => 1,
            'last_sent_at' => Carbon::now()->subSeconds(30),
        ])->canResend());
    }

    public function test_it_allows_another_send_after_the_wait(): void
    {
        $this->assertTrue($this->registration([
            'sent_count' => 1,
            'last_sent_at' => Carbon::now()->subSeconds(180),
        ])->canResend());
    }

    public function test_it_stops_after_five_sends_however_long_you_wait(): void
    {
        $this->assertFalse($this->registration([
            'sent_count' => 5,
            'last_sent_at' => Carbon::now()->subDays(2),
        ])->canResend());
    }

    public function test_confirming_is_reported(): void
    {
        $registration = $this->registration();
        $this->assertFalse($registration->isConfirmed());

        $registration->forceFill(['confirmed_at' => Carbon::now()]);
        $this->assertTrue($registration->isConfirmed());
    }
}
