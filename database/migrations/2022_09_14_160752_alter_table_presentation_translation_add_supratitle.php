<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presentation_translations', static function (Blueprint $table) {
            $table->string('supratitle')->after('locale');
        });

        Schema::table('presentations', static function (Blueprint $table) {
            $table->string('flag')->after('length');
            $table->dropColumn('length');
        });
    }

    public function down(): void
    {
        Schema::table('presentations', static function (Blueprint $table) {
            $table->string('length')->after('ends_at');
            $table->dropColumn('flag');
        });

        Schema::table('presentation_translations', static function (Blueprint $table) {
            $table->dropColumn('supratitle');
        });
    }
};
