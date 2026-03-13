<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organisation_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['gocardless', 'worldpay']);
            $table->boolean('is_active')->default(true);
            // Stores API keys / credentials as an encrypted JSON blob
            $table->text('config')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['organisation_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_configs');
    }
};
