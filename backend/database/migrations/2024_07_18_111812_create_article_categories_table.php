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
        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('seo_name')->unique();
            $table->text('description');
            $table->boolean('live')->default(true);
            $table->boolean('article_live')->default(true);
            $table->integer('section_id');
            $table->integer('tree_left');
            $table->integer('tree_right');
            $table->integer('tree_level');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_categories');
    }
};
