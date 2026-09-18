<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Two kinds of programme item, and the extras the second kind needs.
 *
 * Most items are plain slots you turn up to. A few are exceptions — a workshop
 * or a guided tour — which open a details panel, carry a picture, and have a
 * sign-up form of their own. `type` is that distinction; `bookable` (already
 * here) is now derived from it on save, so the booking flow keeps reading the
 * one flag it always has.
 *
 * The booking form grows a real first and last name — the single `name` column
 * stays, filled from the two, so nothing downstream that prints a name breaks.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('sessions', function (Blueprint $table) {
            // slot | workshop | tour. Default keeps every existing row a slot.
            $table->string('type', 16)->default('slot')->after('kind');
            $table->string('image')->nullable()->after('slug');
        });

        Schema::connection('wcm_2026')->table('session_bookings', function (Blueprint $table) {
            // Nullable: old bookings have only the composed `name`.
            $table->string('first_name')->nullable()->after('registration_id');
            $table->string('last_name')->nullable()->after('first_name');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('sessions', function (Blueprint $table) {
            $table->dropColumn(['type', 'image']);
        });

        Schema::connection('wcm_2026')->table('session_bookings', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
