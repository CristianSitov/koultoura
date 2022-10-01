<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', static function (Blueprint $table) {
            $table->string('type')->default('beholder')->change();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', static function (Blueprint $table) {
            $table->string('type')->default('beholder')->change();
        });
    }
};
