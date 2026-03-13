<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_manual')->default(false);
            $table->boolean('requires_confirmation')->default(false);
            $table->boolean('is_renewal')->default(false);
            $table->char('currency_code', 3)->default('GBP');
            $table->decimal('amount_due', 14, 6)->default(0);
            $table->decimal('amount_paid', 14, 6)->default(0);
            $table->enum('status', [
                'ready', 'pending', 'processing', 'processed', 'cancelled', 'error',
            ])->default('pending');
            $table->date('due_date')->nullable();
            // Unique token for webhook reconciliation
            $table->string('tracking_token')->nullable()->unique();
            $table->string('gateway_customer_id')->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
