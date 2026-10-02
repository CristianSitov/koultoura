<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Who has had the email for a day, sent the evening before. One row per
 * address per day: a send that stops halfway (a closed tab, a refused
 * address) picks up where it left off, and nobody gets it twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->create('day_brief_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_day_id')->constrained('programme_days')->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->string('email');
            $table->timestamp('sent_at');
            $table->unique(['programme_day_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->dropIfExists('day_brief_sends');
    }
};
