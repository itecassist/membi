<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->foreignUuid('organisation_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('payment_gateway_config_id')->nullable()->after('organisation_id')
                ->constrained('payment_gateway_configs')->nullOnDelete();
            $table->enum('class', ['one_off', 'recurring_arrears', 'recurring_advance'])
                ->default('one_off')->after('type');
            $table->boolean('admin_only')->default(false)->after('is_default');
            $table->boolean('requires_confirmation')->default(false)->after('admin_only');
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropForeign(['payment_gateway_config_id']);
            $table->dropForeign(['organisation_id']);
            $table->dropColumn(['payment_gateway_config_id', 'organisation_id', 'class', 'admin_only', 'requires_confirmation']);
        });
    }
};
