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
        Schema::create('virtual_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('virtual_form_id')->constrained()->cascadeOnDelete();
            $table->string('field_name');
            $table->string('description')->nullable();
            $table->boolean('required')->default(false);
            $table->enum('type', ['text', 'number', 'email', 'textarea', 'richtext', 'select', 'checkbox', 'radio', 'date', 'time', 'datetime', 'file', 'image'])->default('text');
            $table->json('options')->nullable();
            $table->boolean('gdpr_sensitive')->default(false);
            $table->boolean('active')->default(true);
            $table->unsignedTinyInteger('sort_order');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_fields');
    }
};
