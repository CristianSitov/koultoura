<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The 2026 programme, which until now lived in a JavaScript file and could only
 * be changed by a deploy.
 *
 * Shape, from the edition itself: four days, three themes — a theme can run
 * over more than one day, so the theme hangs off the day rather than the other
 * way round. The Heritage School is not a theme but an umbrella across days,
 * so it is a flag on the session.
 *
 * Sessions carry their own published flag: the schedule is edited for weeks
 * before it is fit to show, and a half-moved talk should not appear on the
 * public page in the meantime.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->create('themes', function (Blueprint $table) {
            $table->id();
            // Roman, and shown as such: I, II, III.
            $table->string('numeral', 8);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::connection('wcm_2026')->create('theme_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('title')->default('');
            $table->text('description')->nullable();

            $table->unique(['theme_id', 'locale']);
        });

        Schema::connection('wcm_2026')->create('programme_days', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            // Nullable: a day may be added before anyone has decided what it is about.
            $table->foreignId('theme_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('published')->default(false);
            $table->timestamps();
        });

        Schema::connection('wcm_2026')->create('programme_day_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_day_id')->constrained()->cascadeOnDelete();
            $table->string('locale')->index();
            // The weekday as it should read on the page, not as PHP formats it.
            $table->string('name')->default('');
            $table->text('description')->nullable();

            $table->unique(['programme_day_id', 'locale'], 'day_translations_unique');
        });

        Schema::connection('wcm_2026')->create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_day_id')->constrained()->cascadeOnDelete();
            $table->time('starts_at');
            $table->time('ends_at')->nullable();
            // Talk, Conversation, Workshop 3 — free text, because the vocabulary
            // is still being invented.
            $table->string('kind')->default('');
            // Part of the Heritage School, which crosses days and themes.
            $table->boolean('school')->default(false);
            $table->boolean('published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);

            /*
             * A session people have to sign up for separately: the day
             * registration does not cover it, and places run out. Both null for
             * an ordinary talk you simply turn up to.
             */
            $table->string('slug')->nullable()->unique();
            $table->boolean('bookable')->default(false);
            $table->unsignedSmallInteger('capacity')->nullable();

            $table->timestamps();
        });

        Schema::connection('wcm_2026')->create('session_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained()->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('title')->default('');
            $table->string('subtitle')->nullable();
            // Who it is for — "Children, 8–12" — where no named speaker leads it.
            $table->string('audience')->nullable();
            $table->text('description')->nullable();

            $table->unique(['session_id', 'locale']);
        });

        // Who is speaking. Several people can share a session, and one person
        // can appear more than once in the programme.
        Schema::connection('wcm_2026')->create('person_session', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);

            $table->unique(['person_id', 'session_id']);
        });

        /*
         * Places in a capped session. Separate from `registrations`: someone can
         * book a workshop without attending the rest, and the counting has to be
         * per session rather than per day.
         */
        Schema::connection('wcm_2026')->create('session_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained()->cascadeOnDelete();
            // Set when the address matches someone already registered for the
            // days; a booking stands on its own if not.
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('token', 64)->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('locale', 2)->default('en');
            $table->timestamp('confirmed_at')->nullable();
            // Kept rather than deleted: a cancelled place is evidence of demand,
            // and the row is what stops a duplicate booking from the same address.
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            // One place per address per session.
            $table->unique(['session_id', 'email']);
        });
    }

    public function down(): void
    {
        foreach (['session_bookings', 'person_session', 'session_translations', 'sessions',
                  'programme_day_translations', 'programme_days', 'theme_translations', 'themes'] as $table) {
            Schema::connection('wcm_2026')->dropIfExists($table);
        }
    }
};
