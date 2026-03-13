<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('order_payment_id')->nullable()->constrained('order_payments')->nullOnDelete();
            $table->foreignUuid('accounting_code_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->char('currency_code', 3)->default('GBP');
            $table->decimal('debit', 14, 6)->default(0);
            $table->decimal('credit', 14, 6)->default(0);
            $table->boolean('reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            // For future Xero / accounting software sync
            $table->boolean('synced_to_finance')->default(false);
            $table->string('finance_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
