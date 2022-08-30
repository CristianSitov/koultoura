<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', static function (Blueprint $table) {
            $table->rename('people');
        });
    }

    public function down(): void
    {
        Schema::table('people', static function (Blueprint $table) {
            $table->rename('persons');
        });
    }
};
