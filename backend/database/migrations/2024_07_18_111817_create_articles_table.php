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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->integer('type');
            $table->string('title');
            $table->foreignId('article_category_id')->constrained()->onDelete('cascade');
            $table->string('page_title');
            $table->string('seo_name')->unique();
            $table->text('content');
            $table->text('summary');
            $table->string('seo_description');
            $table->boolean('featured');
            $table->boolean('live')->default(true);
            $table->boolean('category_live');
            $table->integer('popularity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
