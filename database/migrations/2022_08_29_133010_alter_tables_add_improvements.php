<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presentations', static function (Blueprint $table) {
            $table->renameColumn('group', 'day_id');
        });

        Schema::create('days', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title');
            $table->string('location');
        });

        Schema::table('speakers', static function (Blueprint $table) {
            $table->rename('persons');
        });

        Schema::table('presentation_speaker', static function (Blueprint $table) {
            $table->rename('presentation_person');
        });

        Schema::table('presentation_person', static function (Blueprint $table) {
            $table->renameColumn('speaker_id', 'person_id');
        });
    }

    public function down(): void
    {
        Schema::table('presentation_person', static function (Blueprint $table) {
            $table->renameColumn('person_id', 'speaker_id');
        });

        Schema::table('presentation_person', static function (Blueprint $table) {
            $table->rename('presentation_speaker');
        });

        Schema::table('persons', static function (Blueprint $table) {
            $table->rename('speakers');
        });

        Schema::drop('days');

        Schema::table('presentations', static function (Blueprint $table) {
            $table->renameColumn('day_id', 'group');
        });
    }
};
