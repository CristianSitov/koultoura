<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('person_presentation', static function (Blueprint $table) {
            $table->integer('person_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('person_presentation', static function (Blueprint $table) {
            $table->integer('person_id')->change();
        });
    }
};
