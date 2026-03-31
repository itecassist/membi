<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix basket_items to support family/group subscriptions:
 *  - Replace single quantity with adult_quantity + junior_quantity
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop FK before touching the unique index so all constraints are cleanly re-applied.
        Schema::table('basket_items', function (Blueprint $table) {
            $table->dropForeign(['basket_id']);
        });

        Schema::table('basket_items', function (Blueprint $table) {
            $table->dropUnique(['basket_id', 'subscription_price_option_id']);
        });

        Schema::table('basket_items', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });

        Schema::table('basket_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('adult_quantity')->default(1);
            $table->unsignedSmallInteger('junior_quantity')->default(0);
        });

        Schema::table('basket_items', function (Blueprint $table) {
            $table->unique(['basket_id', 'subscription_price_option_id']);
            $table->foreign('basket_id')->references('id')->on('baskets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('basket_items', function (Blueprint $table) {
            $table->dropForeign(['basket_id']);
            $table->dropUnique(['basket_id', 'subscription_price_option_id']);
        });

        Schema::table('basket_items', function (Blueprint $table) {
            $table->dropColumn(['adult_quantity', 'junior_quantity']);
        });

        Schema::table('basket_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unique(['basket_id', 'subscription_price_option_id']);
            $table->foreign('basket_id')->references('id')->on('baskets')->cascadeOnDelete();
        });
    }
};
