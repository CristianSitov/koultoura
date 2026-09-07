<?php

namespace App\Console\Commands;

use Database\Seeders\Guests2026Seeder;
use Illuminate\Console\Command;

/*
 * Applies the guest list in Database\Seeders\Guests2026Seeder.
 *
 * This used to read the organisers' Drive folder over the API. It never ran:
 * the folder is private, and putting a key that can read it on a web server
 * was not worth it for a list that changes a few times a year. The texts are
 * transcribed into the seeder instead, which has the advantage of being
 * reviewable in a diff before it reaches the site.
 *
 * Editing a guest in the backoffice is still fine; this puts the seeder's text
 * back over it, which is what it is for.
 */
class SyncGuests extends Command
{
    protected $signature = 'guests:sync {--dry-run : Report what would change without writing anything}';

    protected $description = 'Apply the 2026 guest list from the seeder';

    public function handle(): int
    {
        $guests = Guests2026Seeder::GUESTS;

        if ($this->option('dry-run')) {
            $this->table(
                ['#', 'Guest', 'Role (EN)', 'Bio EN', 'Bio RO'],
                collect($guests)->map(fn ($g) => [
                    $g['position'],
                    $g['full_name'],
                    $g['en']['role'],
                    strlen($g['en']['description']).' chars',
                    strlen($g['ro']['description']).' chars',
                ])->all()
            );

            $this->comment('Dry run: nothing written.');

            return self::SUCCESS;
        }

        (new Guests2026Seeder)->run();

        $this->info(count($guests).' guests applied.');
        $this->line('Portraits are files in public/assets/2026/guests, committed with the code.');

        return self::SUCCESS;
    }
}
