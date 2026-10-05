<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The entrance list sorts and prints people by first and last name, and the
 * form only ever asked for one name. Both stay empty until the office corrects
 * a row; until then they are read off `name` (see Registration::nameParts()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('registrations', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('registrations', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
