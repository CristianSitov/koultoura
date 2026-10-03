<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Workshop and tour places get confirmed. Each workshop has its own switch for
 * emailing new bookings straight away (off until the office turns it on), and
 * each booking remembers when it was last asked — `confirmed_at` was already
 * there, and nothing ever set it, so every place starts unconfirmed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('sessions', function (Blueprint $table) {
            $table->boolean('auto_confirm')->default(false)->after('internal');
        });

        Schema::connection('wcm_2026')->table('session_bookings', function (Blueprint $table) {
            $table->timestamp('confirmation_sent_at')->nullable()->after('confirmed_at');
            $table->unsignedInteger('confirmation_sent_count')->default(0)->after('confirmation_sent_at');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('session_bookings', function (Blueprint $table) {
            $table->dropColumn(['confirmation_sent_at', 'confirmation_sent_count']);
        });

        Schema::connection('wcm_2026')->table('sessions', function (Blueprint $table) {
            $table->dropColumn('auto_confirm');
        });
    }
};
