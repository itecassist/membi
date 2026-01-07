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
        Schema::create('organisation_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->string('primary_color', 7)->default('#000000');
            $table->string('secondary_color', 7)->default('#000000');
            $table->string('button_color', 7)->default('#000000');
            $table->foreignId('tax_rate_id')->constrained()->cascadeOnDelete();
            $table->boolean('admins_require_2fa')->default(false);
            $table->unsignedTinyInteger('max_days_between_2fa');
            $table->string('social_facebook')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('banner')->nullable();
            $table->text('introduction')->nullable();
            $table->text('about')->nullable();
            $table->boolean('show_subscription_button')->default(false); // could be private
            // $table->boolean('show_contact_detail')->default(false);
            $table->boolean('show_events')->default(false);
            $table->boolean('show_new_members')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_configs');
    }
};
