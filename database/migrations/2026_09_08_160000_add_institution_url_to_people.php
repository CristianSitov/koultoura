<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Where a guest's institution can be read about.
 *
 * On the person rather than the translation: the institution's own site is the
 * same address whichever language the page is being read in, even when its name
 * is written differently.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('people', function (Blueprint $table) {
            $table->string('institution_url')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('people', function (Blueprint $table) {
            $table->dropColumn('institution_url');
        });
    }
};
