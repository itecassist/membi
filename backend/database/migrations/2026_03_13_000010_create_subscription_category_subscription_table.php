<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create pivot table linking subscription_categories to subscriptions.
 * One subscription can belong to multiple categories, and vice versa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_category_subscription', function (Blueprint $table) {
            $table->uuid('subscription_category_id');
            $table->uuid('subscription_id');

            $table->primary(['subscription_category_id', 'subscription_id']);

            // Explicit short names to stay within MySQL's 64-char identifier limit
            $table->foreign('subscription_category_id', 'scs_category_id_foreign')
                ->references('id')->on('subscription_categories')->cascadeOnDelete();
            $table->foreign('subscription_id', 'scs_subscription_id_foreign')
                ->references('id')->on('subscriptions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_category_subscription');
    }
};
