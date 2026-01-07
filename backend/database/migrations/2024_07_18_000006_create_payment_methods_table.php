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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->text('explanation');
            $table->boolean('is_active')->default(true);
            $table->boolean('default')->default(true);
            $table->boolean('details')->default(true);
            $table->decimal('surcharge_percentage', 5, 2)->default(0);
            $table->decimal('surcharge_fixed', 10, 2)->default(0);
            $table->foreignId('accounting_code_id')->constrained();
            $table->text('checkout_text')->nullable();
            $table->text('success_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
