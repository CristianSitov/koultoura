<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * A reminder email to everyone registered: written in the backoffice, tested,
 * sent one person at a time, and then followed up — each send keeps the id
 * Resend gave it, so its delivery can be looked up there (delivered, bounced…).
 *
 * A second reminder later is a new row, so it starts with nobody marked sent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->default('');
            $table->string('subject_ro')->nullable();
            $table->text('body')->nullable();
            $table->text('body_ro')->nullable();
            $table->timestamps();
        });

        Schema::connection('wcm_2026')->create('reminder_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reminder_id')->constrained('reminders')->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->string('email');
            // Resend's id for this email; empty where mail is only logged (local).
            $table->string('resend_id')->nullable();
            // sent, then whatever Resend last saw: delivered, bounced, complained,
            // delivery_delayed… — or failed, when it never left.
            $table->string('status', 32);
            $table->text('error')->nullable();
            $table->timestamp('sent_at');
            $table->timestamp('checked_at')->nullable();
            $table->unique(['reminder_id', 'registration_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->dropIfExists('reminder_sends');
        Schema::connection('wcm_2026')->dropIfExists('reminders');
    }
};
