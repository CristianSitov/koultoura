<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The reminder before a workshop or tour: each booking remembers when it was
 * last sent one, so a run that stops halfway resumes where it left off.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('session_bookings', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('confirmation_sent_count');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('session_bookings', function (Blueprint $table) {
            $table->dropColumn('reminder_sent_at');
        });
    }
};
