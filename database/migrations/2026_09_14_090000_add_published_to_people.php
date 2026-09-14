<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Whether a guest is shown on the site.
 *
 * Default true: the guests already in the table are the ones being shown, and
 * a migration that hid all of them on deploy would be a surprise. New guests —
 * and the Drive sync's — start visible too; the button is for taking one down,
 * not for a draft workflow the office did not ask for.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('people', function (Blueprint $table) {
            $table->boolean('published')->default(true)->after('position');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('people', function (Blueprint $table) {
            $table->dropColumn('published');
        });
    }
};
