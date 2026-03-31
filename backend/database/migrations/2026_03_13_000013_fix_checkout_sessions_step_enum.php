<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix checkout_sessions step enum to add missing steps from reference checkout flow:
 *  - review_existing : member reviews their existing subscriptions before adding new ones
 *  - subscription_forms : member fills in subscription-specific custom form fields
 *  - members          : allocate basket items to specific members (group/family checkout)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop the column (drops the CHECK constraint in PostgreSQL)
        Schema::table('checkout_sessions', function (Blueprint $table) {
            $table->dropColumn('step');
        });

        // Re-add with full set of steps
        Schema::table('checkout_sessions', function (Blueprint $table) {
            $table->enum('step', [
                'init',
                'email',
                'review_existing',   // NEW: review existing active subscriptions
                'allocations',
                'members',           // NEW: assign family/group members to slots
                'subscription_forms', // NEW: fill org-defined subscription forms
                'membership_form',
                'payment',
                'review',
                'complete',
            ])->default('init');
        });
    }

    public function down(): void
    {
        Schema::table('checkout_sessions', function (Blueprint $table) {
            $table->dropColumn('step');
        });

        Schema::table('checkout_sessions', function (Blueprint $table) {
            $table->enum('step', [
                'init', 'email', 'allocations', 'membership_form', 'payment', 'review', 'complete',
            ])->default('init');
        });
    }
};
