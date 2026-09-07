<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Switches the office can throw without a deploy.
 *
 * One row per setting, because there is currently one of them — whether the
 * programme section appears on the public page at all — and inventing a table
 * per switch would be worse than a key and a value.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->dropIfExists('settings');
    }
};
