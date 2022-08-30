<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('days', static function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('title');
            $table->dropColumn('location');
        });

        Schema::table('persons', static function (Blueprint $table) {
            $table->dropColumn('role');
            $table->dropColumn('institution');
            $table->dropColumn('description');
        });

        Schema::table('presentations', static function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('description');
        });
    }

    public function down(): void
    {
        Schema::table('days', static function (Blueprint $table) {
            $table->string('name');
            $table->string('title');
            $table->string('location');
        });

        Schema::table('persons', static function (Blueprint $table) {
            $table->string('role');
            $table->string('institution');
            $table->text('description');
        });

        Schema::table('presentations', static function (Blueprint $table) {
            $table->string('title');
            $table->text('description');
        });
    }
};
