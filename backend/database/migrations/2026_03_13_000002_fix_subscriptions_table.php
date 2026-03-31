<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fix subscriptions table to align with reference schema:
 *  - Replace membership_type enum with is_membership boolean
 *  - Rename period → period_type
 *  - Add period_count (integer)
 *  - Add admin_only to published enum
 *  - Add instalments to pricing_type enum
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Replace membership_type with is_membership boolean
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('membership_type');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('is_membership')->default(false);
        });

        // 2. Rename period → period_type
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('period', 'period_type');
        });

        // 3. Add period_count (multiplier, e.g. 2 × year = biennial)
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedSmallInteger('period_count')->default(1);
        });

        // 4. Fix published enum — add admin_only value
        //    In PostgreSQL, $table->enum() creates a VARCHAR CHECK constraint.
        //    Drop column and re-add with expanded values.
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('published');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('published', ['published', 'renewal_only', 'admin_only', 'unpublished'])
                ->default('published');
        });

        // 5. Fix pricing_type enum — add instalments
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('pricing_type');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('pricing_type', ['flat', 'family', 'corporate', 'tiered', 'banded', 'custom_variable', 'instalments'])
                ->default('flat');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['is_membership', 'period_count']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('period_type', 'period');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('published');
            $table->dropColumn('pricing_type');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('membership_type', ['individual', 'group'])->default('individual');
            $table->enum('published', ['published', 'renewal_only', 'unpublished'])->default('published');
            $table->enum('pricing_type', ['flat', 'family', 'corporate', 'tiered', 'custom_variable'])->default('flat');
        });
    }
};
