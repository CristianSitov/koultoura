<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', static function (Blueprint $table) {
            $table->tinyInteger('user_id');
            $table->string('title');
            $table->string('phone');
            $table->string('job');
            $table->string('organization');
            $table->string('area');
            $table->string('country');
            $table->json('event_2021');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
