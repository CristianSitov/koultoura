<?php

use Database\Seeders\Guests2026Seeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * The first-announcement guests, loaded as a migration rather than left to a
 * seeder run by hand: the deploy task runs `migrate --force` and nothing else,
 * so this is the only step that reaches a new environment on its own.
 *
 * The seeder stays the source of truth and is idempotent (it matches on slug),
 * so running it here and again by hand is harmless.
 *
 * Later announcements will NOT arrive this way — this migration runs once. Add
 * them to the seeder and either run it by hand or add another migration like
 * this one.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new Guests2026Seeder())->run();
    }

    public function down(): void
    {
        // Only the rows this seeded; anything added since is left alone.
        $slugs = [
            'oleksandra-kovalchuk', 'andras-mudra', 'barbara-szij', 'raluca-maria-trifa',
            'gabriela-robeci', 'nicoleta-musat', 'iulia-iordan', 'andela-petrovic', 'andreea-lazea',
        ];

        DB::connection('wcm_2026')->table('people')->whereIn('slug', $slugs)->delete();
    }
};
