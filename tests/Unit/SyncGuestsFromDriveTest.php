<?php

namespace Tests\Unit;

use App\Console\Commands\SyncGuestsFromDrive;
use PHPUnit\Framework\TestCase;

/*
 * The folder titles are hand-typed by the organisers and vary a lot, so the
 * parser is the part of the sync most likely to quietly go wrong. These are the
 * real titles from the Drive folder.
 */
class SyncGuestsFromDriveTest extends TestCase
{
    private function parse(string $title): ?array
    {
        return (new SyncGuestsFromDrive())->parseFolderName($title);
    }

    public function test_it_reads_position_name_and_role(): void
    {
        $this->assertSame(
            ['position' => 1, 'name' => 'Oleksandra Kovalchuk', 'role' => 'Speaker & workshop'],
            $this->parse('01. Oleksandra Kovalchuk (MFC UA) - speaker & workshop')
        );
    }

    public function test_it_drops_the_institution_shorthand(): void
    {
        $this->assertSame(
            ['position' => 3, 'name' => 'Barbara Szij', 'role' => ''],
            $this->parse('03. Barbara Szij (KEK HU)')
        );
    }

    public function test_it_survives_stray_whitespace_and_diacritics(): void
    {
        $this->assertSame(
            ['position' => 8, 'name' => 'Anđela Petrović', 'role' => ''],
            $this->parse('08.  Anđela Petrović')
        );
    }

    public function test_it_reads_a_moderator_role(): void
    {
        $this->assertSame(
            ['position' => 5, 'name' => 'Gabriela Robeci', 'role' => 'Moderator'],
            $this->parse('05. Gabriela Robeci (UVT/CICASP) - moderator')
        );
    }

    public function test_it_skips_unnumbered_folders(): void
    {
        $this->assertNull($this->parse('Alexandra Palconi-Sitov (Prin Banat/HoT)'));
        $this->assertNull($this->parse('Casa Voluntarilor'));
    }
}
