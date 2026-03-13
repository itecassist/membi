<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('basket_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('basket_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscription_price_option_id')->constrained()->cascadeOnDelete();
            // For group/family subscriptions, quantity refers to number of members
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['basket_id', 'subscription_price_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('basket_items');
    }
};
