<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix orders table to align with reference schema:
 *  - Add basket_id (link order back to originating basket)
 *  - Add is_subscription_order flag
 *  - Add provisioning_status (tracks post-payment member provisioning)
 *  - Add prev_order_id / next_order_id (renewal chain)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Link to originating basket
            $table->foreignUuid('basket_id')->nullable()->constrained('baskets')->nullOnDelete();

            // True when this order is for subscription(s), not products
            $table->boolean('is_subscription_order')->default(false);

            // Tracks whether member access has been provisioned after payment
            $table->enum('provisioning_status', ['pending', 'provisioning', 'complete', 'failed'])
                ->nullable();

            // Renewal chain — links this order to the previous/next renewal order
            $table->uuid('prev_order_id')->nullable()->index();
            $table->uuid('next_order_id')->nullable()->index();
        });

        // Self-referential FK constraints added separately to avoid circular dependency issues
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('prev_order_id')->references('id')->on('orders')->nullOnDelete();
            $table->foreign('next_order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['prev_order_id']);
            $table->dropForeign(['next_order_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'basket_id',
                'is_subscription_order',
                'provisioning_status',
                'prev_order_id',
                'next_order_id',
            ]);
        });
    }
};
