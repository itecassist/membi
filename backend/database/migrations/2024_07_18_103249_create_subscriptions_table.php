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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('description');
            $table->unsignedBigInteger('virtual_form_id')->nullable();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->enum('membership', ['basic', 'other']);
            $table->enum('membership_type', ['individual', 'group']);
            $table->enum('period', ['daily','weekly','monthly', 'yearly', 'lifetime', 'no_period', 'installments'])->default('yearly');
            $table->enum('renewals', ['fixed_end_date', 'individual_anniversary', 'not_renewable'])->default('fixed_end_date');
            $table->enum('published', ['published', 'renewal_only', 'unpublished'])->default('published');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
