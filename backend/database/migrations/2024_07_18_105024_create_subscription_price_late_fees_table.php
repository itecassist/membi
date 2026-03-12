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
        Schema::create('subscription_price_late_fees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subscription_price_option_id');
            $table->foreign('subscription_price_option_id', 'spo_late_fees_spo_id_fk')
                  ->references('id')->on('subscription_price_options')->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->date('renewal_date');
            $table->decimal('late_fee', 10, 2);
            $table->date('applies_from');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_price_late_fees');
    }
};
