<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix member_subscriptions to align with reference schema:
 *  - Expand status enum with checkout/provisioning states
 *  - Add group_id (for group/family subscriptions)
 *  - Add instance_type (primary holder vs secondary member)
 *  - Add trial and renewability flags
 *  - Add billing date tracking columns
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Expand status enum
        Schema::table('member_subscriptions', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('member_subscriptions', function (Blueprint $table) {
            $table->enum('status', [
                'creating',          // Checkout in progress
                'created',           // Checkout complete, awaiting provisioning
                'provisioning',      // Payment confirmed, provisioning member access
                'active',            // Fully provisioned and active
                'awaiting_approval', // Requires manual admin approval
                'awaiting_payment',  // Invoice issued, payment pending
                'pending',           // Generic pending (legacy)
                'expired',           // Membership period ended
                'cancelled',         // Cancelled by member or admin
                'suspended',         // Temporarily suspended
            ])->default('creating');
        });

        // 2. Add remaining columns
        Schema::table('member_subscriptions', function (Blueprint $table) {
            // Group this subscription belongs to (for family/corporate)
            $table->foreignUuid('group_id')->nullable()->constrained('groups')->nullOnDelete();

            // Whether this is the primary holder or a secondary member in a group sub
            $table->enum('instance_type', ['primary', 'secondary'])->nullable();

            // Trial period flag
            $table->boolean('is_trial')->default(false);

            // Whether this subscription can be renewed
            $table->boolean('is_renewable')->default(true);

            // Billing history
            $table->date('last_billed_at')->nullable();
            $table->date('next_billing_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('member_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'group_id',
                'instance_type',
                'is_trial',
                'is_renewable',
                'last_billed_at',
                'next_billing_date',
            ]);
        });

        Schema::table('member_subscriptions', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('member_subscriptions', function (Blueprint $table) {
            $table->enum('status', ['active', 'pending', 'expired', 'cancelled', 'suspended'])
                ->default('pending');
        });
    }
};
