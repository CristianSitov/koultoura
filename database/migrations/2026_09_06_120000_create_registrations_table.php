<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Attendee registrations for 2026.
 *
 * Deliberately not the 2024 pattern, which created a Laravel User per attendee
 * with a random password: attendees are not accounts, and conflating them put
 * event data in the shared users table. This is its own table in the 2026
 * database, with a token so an address can actually be verified — 2024 sent an
 * informational email and never confirmed anything.
 *
 * `days` is which days someone plans to attend; `workshop_interest` is a
 * non-binding "I would like a workshop place", allocated by hand. Neither
 * reserves a seat — there is no capacity model here yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('organisation')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->json('days');
            $table->boolean('workshop_interest')->default(false);
            // Which language to write to them in.
            $table->string('locale', 5)->default('en');
            $table->timestamp('confirmed_at')->nullable();
            // When they ticked the consent box, and how many times we have
            // written — both are things you want on record for a mailing list.
            $table->timestamp('consented_at');
            $table->timestamp('last_sent_at')->nullable();
            $table->unsignedTinyInteger('sent_count')->default(0);
            $table->timestamps();

            $table->index('confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->dropIfExists('registrations');
    }
};
