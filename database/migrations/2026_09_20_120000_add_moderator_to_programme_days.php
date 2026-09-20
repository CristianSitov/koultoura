<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Each day of the programme can have a moderator — the person who holds the
 * room across its sessions. They are a Person like any speaker, either one
 * already on the grid or one added just for this, kept off the public list.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('programme_days', function (Blueprint $table) {
            $table->foreignId('moderator_id')->nullable()->after('theme_id')
                ->constrained('people')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('programme_days', function (Blueprint $table) {
            $table->dropConstrainedForeignId('moderator_id');
        });
    }
};
