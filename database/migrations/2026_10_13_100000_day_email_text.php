<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The day-before email gets its own words, written like the reminder's:
 * subject and text, English and Romanian. Kept apart from "About this day",
 * which is the day page's text on the public site.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('programme_days', function (Blueprint $table) {
            $table->string('email_subject')->nullable();
            $table->string('email_subject_ro')->nullable();
            $table->text('email_body')->nullable();
            $table->text('email_body_ro')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('programme_days', function (Blueprint $table) {
            $table->dropColumn(['email_subject', 'email_subject_ro', 'email_body', 'email_body_ro']);
        });
    }
};
