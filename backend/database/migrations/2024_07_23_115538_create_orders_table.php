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
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('organisation_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->foreignUuid('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_reference')->nullable();
            $table->enum('status', [
                'pending', 'payment_received', 'payment_problem',
                'cancelled', 'no_payment_required', 'completed',
                'partial_payment', 'refunded'
            ])->default('pending');
            $table->date('date_placed');
            $table->date('date_finished')->nullable();
            $table->text('comments')->nullable();
            $table->char('currency_code', 3)->default('GBP');
            $table->decimal('tax_total', 14, 6)->default(0);
            $table->decimal('total', 14, 6)->default(0);
            $table->softDeletes();
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
