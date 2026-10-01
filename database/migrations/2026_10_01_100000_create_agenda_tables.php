<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The internal agenda: what speakers, guests and the team are sent, which is
 * the public programme plus the things arranged only for them — meetings,
 * meals, round tables, outings.
 *
 * Events hang off a programme day, so they move with it. Romanian is optional
 * on every text, as elsewhere: left empty, the page falls back to English.
 *
 * Recipients are their own list because a speaker has no address on record —
 * `people` is what the public grid shows, and carries no email.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->create('agenda_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_day_id')->constrained('programme_days')->cascadeOnDelete();
            $table->time('starts_at');
            $table->time('ends_at')->nullable();
            $table->string('title');
            $table->string('title_ro')->nullable();
            $table->string('location')->nullable();
            $table->string('location_ro')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ro')->nullable();
            $table->timestamps();
        });

        Schema::connection('wcm_2026')->create('agenda_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            // The language of the email and of the page it links to.
            $table->string('locale', 2)->default('en');
            // In the link, so the page needs no login: it was sent to one address.
            $table->string('token', 48)->unique();
            $table->timestamp('last_sent_at')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->dropIfExists('agenda_recipients');
        Schema::connection('wcm_2026')->dropIfExists('agenda_events');
    }
};
