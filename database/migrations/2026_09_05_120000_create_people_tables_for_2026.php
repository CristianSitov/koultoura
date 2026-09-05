<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The 2026 guests, in the same people/person_translations pair the 2022 event
 * used, so App\Models\Person works across years. What is new here is the
 * provenance: each row remembers the Drive folder it was imported from, so the
 * sync can match a person back to their folder after a rename.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->create('people', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('slug')->unique();
            $table->string('avatar')->default('');
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('drive_folder_id')->nullable()->unique();
            $table->string('drive_photo_id')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('wcm_2026')->create('person_translations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('person_id')->unsigned();
            $table->string('locale')->index();
            $table->string('role')->default('');
            $table->string('institution')->default('');
            $table->text('description')->nullable();

            $table->unique(['person_id', 'locale']);
            $table->foreign('person_id')
                ->references('id')
                ->on('people')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->dropIfExists('person_translations');
        Schema::connection('wcm_2026')->dropIfExists('people');
    }
};
