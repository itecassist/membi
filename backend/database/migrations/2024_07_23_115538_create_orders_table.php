<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained();
            $table->foreignId('organisation_id')->constrained();
            $table->string('name');
            $table->string('email');
            $table->string('payment_method'); // when paypal/gocardless the status should change to payment_received/payment_problem
            $table->string('payment_reference')->nullable();
            $table->enum('order_status', ['order_placed', 'payment_received', 'payment_problem', 'cancelled', 'cancelled_before_payment', 'cancelled_pending_payment', 'cancelled_refund_scheduled', 'cancelled_refund_due', 'cancelled_not_refunded', 'cancelled_refunded', 'partially_cancelled', 'no_payment_required', 'completed', 'partial_payment', 'refunded', 'payment_transfer']);
            $table->date('date_finished');
            $table->string('comments')->nullable();
            $table->char('currency_code', 3);
            $table->decimal('currency_value', 14, 6)->nullable();
            $table->decimal('tax_total', 14, 6);
            $table->decimal('total', 14, 6);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
