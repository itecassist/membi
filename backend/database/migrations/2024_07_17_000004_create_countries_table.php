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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('iso_code_2', 2);
            $table->char('iso_code_3', 3);
            $table->char('currency_code', 3);
            $table->char('currency_symbol', 1);
            $table->boolean('symbol_left')->default(true);
            $table->char('decimal_place', 1)->default(2);
            $table->char('decimal_point', 1)->default(".");
            $table->char('thousands_point', 1)->default(",");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
