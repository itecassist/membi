<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add locale, currency, and subscription_description to organisations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            // Default currency for the organisation
            $table->char('currency', 3)->default('GBP');

            // BCP-47 locale tag (e.g. en-GB, en-US, fr-FR)
            $table->string('locale', 10)->default('en-GB');

            // Custom description shown on public subscription browse page
            $table->text('subscription_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn(['currency', 'locale', 'subscription_description']);
        });
    }
};
