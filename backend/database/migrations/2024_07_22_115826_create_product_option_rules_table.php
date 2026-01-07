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
        Schema::create('product_option_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('model')->comment('Table to be checked');
            $table->string('field')->comment('Field to be checked');
            $table->string('operator')->comment('Operator to be used');
            $table->string('value')->comment('Value to be checked');
            $table->string('action_option_id')->comment('Action to be taken on the option');
            $table->boolean('auto')->comment('Should happen without consent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_option_rules');
    }
};
