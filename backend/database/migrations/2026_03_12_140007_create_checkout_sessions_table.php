<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('basket_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignUuid('organisation_id')->constrained()->cascadeOnDelete();
            $table->enum('step', [
                'init', 'email', 'allocations', 'membership_form', 'payment', 'review', 'complete',
            ])->default('init');
            $table->enum('mode', ['public', 'member', 'admin'])->default('member');
            // For public/guest checkouts before the member logs in
            $table->string('initiator_email')->nullable();
            // JSON: [{ basketItemId, memberId|groupId }]
            $table->json('allocations')->nullable();
            // JSON: { purchaserData, membersToCreate, subscriptionForms }
            $table->json('forms')->nullable();
            // Set once createOrder() is called
            $table->uuid('order_id')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->nullable();
            // JSON map of step → 'complete'|'skipped'
            $table->json('area_status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_sessions');
    }
};
