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
        Schema::create('organisation_config_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->boolean('should_authorize_members')->default(false);
            $table->boolean('require_2fa')->default(false);
            $table->unsignedTinyInteger('max_days_between_2fa');
            $table->boolean('require_physical_address')->default(false);
            $table->boolean('require_physical_address_for_groups')->default(false);
            $table->boolean('has_junior_members')->default(false);
            $table->unsignedTinyInteger('junior_member_max_age')->default(18);
            $table->boolean('junior_member_auto_renew_to_adult')->default(false);

            //$table->boolean('junior_member_can_sign_declaration')->default(false);
            $table->boolean('has_family_membership')->default(false); /// ABC0001
            $table->unsignedTinyInteger('family_membership_max_adults')->default(2);
            $table->unsignedTinyInteger('family_membership_max_juniors')->default(2);
            // To what should this default to on renewal?
            $table->boolean('has_group_members')->default(false); /// ABC0001
            $table->boolean('does_each_group_member_have_membership_number')->default(true); /// ABC0001-1, ABC0001-2
            $table->boolean('has_membership_numbers')->default(true);
            $table->boolean('does_membership_numbers_auto_increment')->default(true);
            $table->boolean('can_member_sign_declaration_for_other_adult_members')->default(false);
            $table->boolean('prompt_admin_to_remove_inactive_members')->default(true);
            $table->unsignedInteger('max_days_inactive')->default(365);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_config_members');
    }
};
