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
        Schema::create('organisation_config_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_member_have_more_than_one_subscription')->default(false);
            $table->boolean('can_have_subscription_without_membership')->default(false);
            $table->unsignedTinyInteger('recently_expired_annual_subscription_months')->default(1);
            $table->unsignedTinyInteger('recently_expired_monthly_subscription_days')->default(30);
            $table->unsignedTinyInteger('recently_expired_other_period_days')->default(30);
            $table->unsignedTinyInteger('renew_annual_subscription_months')->default(1);
            $table->unsignedTinyInteger('renew_monthly_subscription_days')->default(30);
            $table->unsignedTinyInteger('renew_other_subscription_days')->default(30);
            $table->boolean('forced_joining_fee')->default(true);
            $table->unsignedBigInteger('subscription_joining_id')->nullable();
            $table->unsignedTinyInteger('auto_renewal_order_days')->default(5);
            // $table->boolean('forms_options')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_config_subscriptions');
    }
};
