<?php

namespace Tests\Unit;

use App\Models\ProgrammeDay;
use App\Models\Session;
use App\Support\Agenda;
use Tests\TestCase;

/*
 * The programme box on the internal agenda: its hours. No database — the day
 * and its sessions are made in memory.
 */
class AgendaHoursTest extends TestCase
{
    private function day(array $sessions, array $attributes = []): ProgrammeDay
    {
        return (new ProgrammeDay)
            ->forceFill($attributes + ['published' => true])
            ->setRelation('sessions', collect($sessions)->map(
                fn (array $s) => (new Session)->forceFill($s + ['published' => true])
            ));
    }

    public function test_hours_run_from_the_first_start_to_the_last_end(): void
    {
        // The last session has no end, so it is given an hour: 18:00 → 19:00.
        $hours = Agenda::hours($this->day([
            ['starts_at' => '09:15:00', 'ends_at' => null],
            ['starts_at' => '11:00:00', 'ends_at' => '12:30:00'],
            ['starts_at' => '18:00:00', 'ends_at' => null],
            ['starts_at' => '20:00:00', 'ends_at' => null, 'published' => false],
        ]));

        $this->assertSame(['09:15', '19:00', 3], [$hours['start'], $hours['end'], $hours['sessions']]);
    }

    public function test_a_long_early_session_can_be_the_last_to_end(): void
    {
        $hours = Agenda::hours($this->day([
            ['starts_at' => '10:00:00', 'ends_at' => '18:30:00'],
            ['starts_at' => '16:00:00', 'ends_at' => null],
        ]));

        $this->assertSame('18:30', $hours['end']);
    }

    public function test_hours_set_by_hand_win_and_auto_is_kept(): void
    {
        $hours = Agenda::hours($this->day(
            [['starts_at' => '09:15:00', 'ends_at' => '18:30:00']],
            ['agenda_starts_at' => '08:45:00'],
        ));

        $this->assertSame(['08:45', '18:30'], [$hours['start'], $hours['end']]);
        $this->assertSame('09:15', $hours['auto']['start']);
    }

    public function test_a_day_with_nothing_published_has_no_box(): void
    {
        $this->assertNull(Agenda::hours($this->day([['starts_at' => '09:00:00', 'published' => false]])));
        $this->assertNull(Agenda::hours($this->day([['starts_at' => '09:00:00']], ['published' => false])));
    }
}
