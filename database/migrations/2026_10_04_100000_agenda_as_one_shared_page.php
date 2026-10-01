<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The agenda becomes one page behind one secret link, which the office sends
 * from its own mail. So what was built for sending it person by person goes:
 * the send list, and the address kept on a speaker.
 *
 * What a day gains instead is its programme box: the note written on it (when
 * to arrive, where speakers eat) and, optionally, its hours — left empty, they
 * follow the programme, from the first session's start to the last one's end.
 *
 * The link's code is a row in `settings`, so it needs no column here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->dropIfExists('agenda_recipients');

        Schema::connection('wcm_2026')->table('people', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'email_locale']);
        });

        Schema::connection('wcm_2026')->table('programme_days', function (Blueprint $table) {
            $table->time('agenda_starts_at')->nullable()->after('published');
            $table->time('agenda_ends_at')->nullable()->after('agenda_starts_at');
            $table->text('agenda_note')->nullable()->after('agenda_ends_at');
            $table->text('agenda_note_ro')->nullable()->after('agenda_note');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('programme_days', function (Blueprint $table) {
            $table->dropColumn(['agenda_starts_at', 'agenda_ends_at', 'agenda_note', 'agenda_note_ro']);
        });

        Schema::connection('wcm_2026')->table('people', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('full_name');
            $table->string('email_locale', 2)->default('en')->after('email');
        });

        Schema::connection('wcm_2026')->create('agenda_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->nullable()->unique()->constrained('people')->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('locale', 2)->default('en');
            $table->string('token', 48)->unique();
            $table->timestamp('last_sent_at')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->string('sent_email')->nullable();
            $table->timestamps();
        });
    }
};
