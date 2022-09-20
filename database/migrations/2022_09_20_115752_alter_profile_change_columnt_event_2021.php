<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', static function (Blueprint $table) {
            $table->renameColumn('event_2021', 'event_details');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', static function (Blueprint $table) {
            $table->renameColumn('event_details', 'event_2021');
        });
    }
};
