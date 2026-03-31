<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create order_subscription_items table.
 *
 * Dedicated subscription line items within an order — separate from generic
 * order_items so subscription-specific data (dates, quantities, member links)
 * can be stored without forcing nullable columns onto the generic table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_subscription_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscription_price_option_id')
                ->nullable()->constrained()->nullOnDelete();

            // The provisioned subscription instance (set after provisioning)
            $table->foreignUuid('member_subscription_id')
                ->nullable()->constrained('member_subscriptions')->nullOnDelete();

            // Quantities (adult + junior for family options)
            $table->unsignedSmallInteger('adult_quantity')->default(1);
            $table->unsignedSmallInteger('junior_quantity')->default(0);

            // Pricing at time of order (snapshot — not live FK to price option)
            $table->decimal('unit_price', 14, 6)->default(0);
            $table->decimal('subtotal', 14, 6)->default(0);
            $table->char('currency_code', 3)->default('GBP');

            // Membership period covered by this line item
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_subscription_items');
    }
};
