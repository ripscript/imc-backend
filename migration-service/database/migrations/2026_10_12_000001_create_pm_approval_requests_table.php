<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('content_detail_projects')->cascadeOnDelete();
            $table->string('approvable_type', 50);
            $table->unsignedBigInteger('approvable_id');
            $table->string('type', 50);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'type'], 'idx_approvals_status_type');
            $table->index(['approvable_type', 'approvable_id'], 'idx_approvals_approvable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_approval_requests');
    }
};
