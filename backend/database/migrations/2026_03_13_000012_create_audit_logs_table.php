<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create audit_logs table.
 * Records admin and member actions for compliance and traceability.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Scope: which org this log entry belongs to
            $table->foreignUuid('organisation_id')->nullable()->constrained()->nullOnDelete();

            // Who performed the action (user account)
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Which member profile performed the action (if applicable)
            $table->foreignUuid('member_id')->nullable()->constrained('members')->nullOnDelete();

            // Action performed, e.g. 'member.created', 'subscription.renewed', 'order.placed'
            $table->string('action');

            // Polymorphic subject of the action
            $table->string('subject_type')->nullable();
            $table->uuid('subject_id')->nullable();

            // Before/after snapshots
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Request context
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Read-only table — no updated_at needed
            $table->index(['organisation_id', 'action']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
