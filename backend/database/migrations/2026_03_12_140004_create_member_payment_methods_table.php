<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('payment_method_id')->constrained()->cascadeOnDelete();
            // Human-readable label (e.g. "GoCardless mandate", "Visa ending 4242")
            $table->string('label')->nullable();
            // Gateway-specific reference (mandate ID, card token, etc.)
            $table->string('gateway_reference')->nullable();
            // Additional encrypted metadata from the gateway
            $table->text('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_payment_methods');
    }
};
