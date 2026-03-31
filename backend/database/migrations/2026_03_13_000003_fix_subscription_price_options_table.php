<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix subscription_price_options table to align with reference schema:
 *  - Rename name → label
 *  - Expand pricing_type enum with individual/family/corporate
 *  - Add pricing_config (JSON — stores tier rules, family caps, etc.)
 *  - Add currency_code, setup_price, max_members
 *  - Add instance_type (primary/secondary for group allocations)
 *  - Add feature flags: use_pro_rata, allow_instalments, offer_trial
 *  - Add rollover_period_days
 *  - Add is_active boolean (convenience API field alongside published)
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename name → label
        Schema::table('subscription_price_options', function (Blueprint $table) {
            $table->renameColumn('name', 'label');
        });

        // 2. Expand pricing_type enum
        Schema::table('subscription_price_options', function (Blueprint $table) {
            $table->dropColumn('pricing_type');
        });

        Schema::table('subscription_price_options', function (Blueprint $table) {
            $table->enum('pricing_type', [
                'flat', 'individual', 'family', 'corporate', 'tiered', 'banded', 'custom_variable',
            ])->default('flat');
        });

        // 3. Add all missing columns
        Schema::table('subscription_price_options', function (Blueprint $table) {
            // The pricing rules payload — shape depends on pricing_type
            $table->json('pricing_config')->nullable();

            // Currency for this option (can differ per option in multi-currency orgs)
            $table->char('currency_code', 3)->default('GBP');

            // One-off setup/joining fee charged at first purchase
            $table->decimal('setup_price', 10, 2)->nullable();

            // For group subscriptions: is this the primary holder or a secondary member slot?
            $table->enum('instance_type', ['primary', 'secondary'])->nullable();

            // Hard cap on members for family/corporate options (null = unlimited)
            $table->unsignedSmallInteger('max_members')->nullable();

            // Pro-rata pricing for mid-year joins
            $table->boolean('use_pro_rata')->default(false);

            // Allow instalment payment schedules
            $table->boolean('allow_instalments')->default(false);

            // Offer a trial period for new members
            $table->boolean('offer_trial')->default(false);

            // Days after start of new membership year that rollover pricing applies
            $table->unsignedSmallInteger('rollover_period_days')->nullable();

            // Convenience boolean for API — true = published, false = unpublished
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('subscription_price_options', function (Blueprint $table) {
            $table->renameColumn('label', 'name');
        });

        Schema::table('subscription_price_options', function (Blueprint $table) {
            $table->dropColumn('pricing_type');
        });

        Schema::table('subscription_price_options', function (Blueprint $table) {
            $table->enum('pricing_type', ['flat', 'tiered', 'custom_variable'])->default('flat');
        });

        Schema::table('subscription_price_options', function (Blueprint $table) {
            $table->dropColumn([
                'pricing_config',
                'currency_code',
                'setup_price',
                'instance_type',
                'max_members',
                'use_pro_rata',
                'allow_instalments',
                'offer_trial',
                'rollover_period_days',
                'is_active',
            ]);
        });
    }
};
