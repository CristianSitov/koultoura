<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * A programme title can lead somewhere: the editor gives a session an address
 * and its title becomes a link, opened in a new tab. One address for both
 * languages. A workshop or a tour has none — its title opens its own panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('sessions', function (Blueprint $table) {
            $table->string('link', 2048)->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('sessions', function (Blueprint $table) {
            $table->dropColumn('link');
        });
    }
};
