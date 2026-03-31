<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK before modifying the column so the constraint is cleanly re-applied
        // with nullOnDelete (previous migration used cascadeOnDelete).
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Make user_id nullable — supports admin-created members without user accounts
        Schema::table('members', function (Blueprint $table) {
            $table->uuid('user_id')->nullable()->change();
        });

        // Re-add FK with nullOnDelete: when a user is deleted, set member.user_id to NULL
        Schema::table('members', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('members', function (Blueprint $table) {
            $table->uuid('user_id')->nullable(false)->change();
        });

        Schema::table('members', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
