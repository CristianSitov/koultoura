<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentations', static function (Blueprint $table) {
            $table->id();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('title');
            $table->string('length');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentations');
    }
};
