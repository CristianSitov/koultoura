<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('person_presentation', static function (Blueprint $table) {
            $table->string('person_type')->after('person_id');
            $table->integer('order')->after('person_id');
        });
    }

    public function down(): void
    {
        Schema::table('person_presentation', static function (Blueprint $table) {
            $table->dropColumn('person_type');
            $table->dropColumn('order');
        });
    }
};
