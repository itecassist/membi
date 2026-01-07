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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('product_option_id')->constrained();
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->unsignedBigInteger('shipment_id')->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('price', 14, 6);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
