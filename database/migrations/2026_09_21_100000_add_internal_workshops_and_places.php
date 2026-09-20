<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * An internal workshop is not open to the public — it appears on the programme
 * under a lock, and its seats are handed out by the office. Each seat is a
 * "place" with a short readable code; the office fills in an email, sends an
 * invitation, and on confirmation the person is sent the calendar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('sessions', function (Blueprint $table) {
            $table->boolean('internal')->default(false)->after('bookable');
        });

        Schema::connection('wcm_2026')->create('session_places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('sessions')->cascadeOnDelete();
            // Short, readable, unique — what the office and the guest refer to.
            $table->string('code', 6)->unique();
            // Filled by the office; the person invited to hold this place.
            $table->string('email')->nullable();
            // In the confirmation link, so the place can be claimed from an email.
            $table->string('token', 48)->unique();
            // open → invited → confirmed.
            $table->string('status', 16)->default('open');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->dropIfExists('session_places');

        Schema::connection('wcm_2026')->table('sessions', function (Blueprint $table) {
            $table->dropColumn('internal');
        });
    }
};
