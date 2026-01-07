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
        Schema::create('subscription_price_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->enum('eligibility', ['individual', 'adult', 'junior', 'family_individual_in_a_family', 'corporate_non_family'])->default('individual');
            $table->enum('price_option', ['flat_price', 'number_of_subscriptions', 'custom_variable'])->default('flat_price');
            $table->string('price_option_name');
            $table->enum('published', ['published', 'renewals_only', 'un_published'])->default('published');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_price_options');
    }
};
