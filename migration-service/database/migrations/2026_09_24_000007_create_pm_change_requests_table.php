<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('content_detail_projects')->cascadeOnDelete();
            $table->foreignId('project_stage_id')->nullable()->constrained('pm_project_stages')->nullOnDelete();
            $table->string('change_no', 50)->unique();
            $table->string('title');
            $table->text('reason')->nullable();
            $table->text('impact_scope')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_change_requests');
    }
};
