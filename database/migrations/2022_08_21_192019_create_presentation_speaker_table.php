<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentation_speaker', static function (Blueprint $table) {
            $table->id();
            $table->integer('presentation_id');
            $table->integer('speaker_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentation_speaker');
    }
};
