<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * A registration can be taken off — someone who registered twice, or asked
 * to be removed — without losing it: it leaves every count, list and email,
 * and the office can bring it back. Payments stay linked to it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('registrations', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('registrations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
