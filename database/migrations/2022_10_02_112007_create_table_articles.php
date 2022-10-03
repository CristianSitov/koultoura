<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', static function (Blueprint $table) {
            $table->id();
            $table->ulid('canonical');
            $table->date('published_at');
            $table->timestamps();
        });
        Schema::create('article_translations', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->string('locale')->index();
            $table->text('slug');
            $table->text('title');
            $table->text('excerpt');
            $table->unsignedBigInteger('featured_id');
            $table->text('content');

            $table->unique(['article_id', 'locale']);
            $table->foreign('article_id')
                ->references('id')
                ->on('articles')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_translations');
        Schema::dropIfExists('articles');
    }
};
