<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Some workshops are for young people. Those ask the attendee's age, and when
 * it is under 18 the booking is made by a parent or guardian: the child stays
 * the beneficiary (the name on the booking), but the guardian's name, phone and
 * a written consent are recorded against it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('sessions', function (Blueprint $table) {
            $table->boolean('youth')->default(false)->after('school');
        });

        Schema::connection('wcm_2026')->table('session_bookings', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->after('phone');
            $table->string('guardian_name')->nullable()->after('age');
            $table->string('guardian_phone')->nullable()->after('guardian_name');
            $table->string('guardian_consent')->nullable()->after('guardian_phone');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('sessions', function (Blueprint $table) {
            $table->dropColumn('youth');
        });

        Schema::connection('wcm_2026')->table('session_bookings', function (Blueprint $table) {
            $table->dropColumn(['age', 'guardian_name', 'guardian_phone', 'guardian_consent']);
        });
    }
};
