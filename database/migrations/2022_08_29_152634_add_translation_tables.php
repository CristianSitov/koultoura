<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('day_translations', static function (Blueprint $table) {
            $table->id();
            $table->bigInteger('day_id')->unsigned();
            $table->string('locale')->index();
            $table->string('name');
            $table->string('title');
            $table->string('location');

            $table->unique(['day_id', 'locale']);
            $table->foreign('day_id')
                ->references('id')
                ->on('days')
                ->onDelete('cascade');
        });

        Schema::create('person_translations', static function (Blueprint $table) {
            $table->id();
            $table->bigInteger('person_id')->unsigned();
            $table->string('locale')->index();
            $table->string('role');
            $table->string('institution');
            $table->text('description');

            $table->unique(['person_id', 'locale']);
            $table->foreign('person_id')
                ->references('id')
                ->on('persons')
                ->onDelete('cascade');
        });

        Schema::create('presentation_translations', static function (Blueprint $table) {
            $table->id();
            $table->bigInteger('presentation_id')->unsigned();
            $table->string('locale')->index();
            $table->string('title');
            $table->string('subtitle');
            $table->text('description');

            $table->unique(['presentation_id', 'locale']);
            $table->foreign('presentation_id')
                ->references('id')
                ->on('presentations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::drop('day_translations');
        Schema::drop('person_translations');
        Schema::drop('presentation_translations');
    }
};
