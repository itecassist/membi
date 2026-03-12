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
            $table->uuid('id')->primary();
            $table->foreignUuid('organisation_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->uuid('virtual_form_id')->nullable();
            $table->uuid('document_id')->nullable();
            $table->enum('membership_type', ['individual', 'group'])->default('individual');
            $table->enum('period', ['day', 'week', 'month', 'year', 'lifetime', 'none', 'instalments'])->default('year');
            $table->enum('renewal_type', ['auto_renew', 'manual', 'not_renewable'])->default('manual');
            $table->enum('pricing_type', ['flat', 'family', 'corporate', 'tiered', 'custom_variable'])->default('flat');
            $table->enum('published', ['published', 'renewal_only', 'unpublished'])->default('published');
            $table->boolean('is_joining_fee')->default(false);
            $table->softDeletes();
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
