<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_project_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('content_detail_projects')->cascadeOnDelete();
            $table->foreignId('workflow_stage_id')->nullable()->constrained('pm_workflow_stages')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('sequence');
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('progress')->default(0);
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('depends_on_project_stage_id')->nullable()->constrained('pm_project_stages')->nullOnDelete();
            $table->boolean('is_required')->default(true);
            $table->boolean('allow_skip')->default(false);
            $table->boolean('allow_repeat')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_configured')->default(false);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['project_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_project_stages');
    }
};
