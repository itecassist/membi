<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create subscription_instance_allocations table.
 *
 * Tracks which members are allocated to which group/family subscription instance.
 * For a family subscription, the primary holder is in member_subscriptions and each
 * dependent member gets an allocation row here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_instance_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // The parent subscription instance (belongs to primary holder)
            $table->foreignUuid('member_subscription_id')
                ->constrained('member_subscriptions')->cascadeOnDelete();

            // The member allocated to this instance slot
            $table->foreignUuid('member_id')->constrained('members')->cascadeOnDelete();

            // Optional: the group the allocation belongs to
            $table->foreignUuid('group_id')->nullable()->constrained('groups')->nullOnDelete();

            $table->timestamp('allocated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['member_subscription_id', 'member_id'], 'sia_sub_member_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_instance_allocations');
    }
};
