<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('wcm_2024')
            ->create('venues', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        Schema::connection('wcm_2024')
            ->create('venue_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('venue_id');
                $table->string('locale')->index();
                $table->string('location');

                $table->unique(['venue_id', 'locale']);
                $table->timestamps();
            });
        Schema::connection('wcm_2024')
            ->table('presentations', function (Blueprint $table) {
                $table->unsignedInteger('venue_id')
                    ->after('day_id')
                    ->references('id')
                    ->on('venues');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('wcm_2024')
            ->table('presentations', function (Blueprint $table) {
                $table->dropColumn('venue_id');
            });
        Schema::connection('wcm_2024')
            ->dropIfExists('venue_translations');
        Schema::connection('wcm_2024')
            ->dropIfExists('venues');
    }
};
