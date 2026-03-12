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
        Schema::create('faq_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('faq_id')->constrained()->cascadeOnDelete();
            $table->string('tag')->unique();
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->unsignedTinyInteger('tag_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_tags');
    }
};
