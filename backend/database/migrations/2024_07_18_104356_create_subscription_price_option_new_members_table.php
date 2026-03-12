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
        Schema::create('subscription_price_option_new_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subscription_price_option_id');
            $table->foreign('subscription_price_option_id', 'spo_new_members_spo_id_fk')
                  ->references('id')->on('subscription_price_options')->cascadeOnDelete();
            $table->boolean('enable_rollover')->default(false);
            $table->integer('rollover_period_days')->default(0);
            $table->boolean('enable_pro_rata_pricing')->default(false);
            $table->decimal('pro_rata_pricing', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_price_option_new_members');
    }
};
