<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The same evidence for every bulk email as the reminder has: Resend's id,
 * what Resend last saw (delivered, bounced…), and a send that failed, kept.
 *
 * The day-before email gets the columns on its own table; the workshop
 * reminder gets a table of its own — one row per send, so "Remind again"
 * adds to the history instead of overwriting it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('day_brief_sends', function (Blueprint $table) {
            $table->string('resend_id')->nullable()->after('email');
            $table->string('status', 32)->default('sent')->after('resend_id');
            $table->text('error')->nullable()->after('status');
            $table->timestamp('checked_at')->nullable()->after('sent_at');
        });

        Schema::connection('wcm_2026')->create('session_reminder_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('sessions')->cascadeOnDelete();
            // One or the other: a public booking, or a place in an internal workshop.
            $table->foreignId('session_booking_id')->nullable()->constrained('session_bookings')->cascadeOnDelete();
            $table->foreignId('session_place_id')->nullable()->constrained('session_places')->cascadeOnDelete();
            $table->string('email');
            $table->string('resend_id')->nullable();
            $table->string('status', 32);
            $table->text('error')->nullable();
            $table->timestamp('sent_at');
            $table->timestamp('checked_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->dropIfExists('session_reminder_sends');

        Schema::connection('wcm_2026')->table('day_brief_sends', function (Blueprint $table) {
            $table->dropColumn(['resend_id', 'status', 'error', 'checked_at']);
        });
    }
};
