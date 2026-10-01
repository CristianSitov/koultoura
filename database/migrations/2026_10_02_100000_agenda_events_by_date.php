<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * An agenda event happens on a date, not on a programme day. Guests land the
 * day before the symposium and leave the day after, and those days have their
 * own dinners and transfers — but they are not programme days, and inventing
 * two empty ones would put stray columns on the public programme and renumber
 * "Day 1" to "Day 4".
 *
 * Events already entered keep their place: each takes the date of the day it
 * was attached to before the link to that day is dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('agenda_events', function (Blueprint $table) {
            $table->date('date')->nullable()->after('id');
        });

        DB::connection('wcm_2026')->statement(
            'UPDATE agenda_events e JOIN programme_days d ON d.id = e.programme_day_id SET e.date = d.date'
        );

        Schema::connection('wcm_2026')->table('agenda_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('programme_day_id');
        });

        // Raw, so the migration needs no doctrine/dbal to alter a column.
        DB::connection('wcm_2026')->statement('ALTER TABLE agenda_events MODIFY `date` DATE NOT NULL');

        Schema::connection('wcm_2026')->table('agenda_events', function (Blueprint $table) {
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('agenda_events', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->foreignId('programme_day_id')->nullable()->after('id')->constrained('programme_days')->cascadeOnDelete();
        });

        DB::connection('wcm_2026')->statement(
            'UPDATE agenda_events e JOIN programme_days d ON d.date = e.date SET e.programme_day_id = d.id'
        );

        // An event on a day either side of the symposium has no programme day to
        // go back to, and the old shape cannot hold it.
        DB::connection('wcm_2026')->table('agenda_events')->whereNull('programme_day_id')->delete();

        Schema::connection('wcm_2026')->table('agenda_events', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }
};
