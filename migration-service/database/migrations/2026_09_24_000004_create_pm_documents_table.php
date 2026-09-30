<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('content_detail_projects')->cascadeOnDelete();
            $table->foreignId('project_stage_id')->nullable()->constrained('pm_project_stages')->cascadeOnDelete();
            $table->foreignId('document_type_id')->nullable()->constrained('pm_document_types')->nullOnDelete();
            $table->string('file_name');
            $table->text('file_path');
            $table->unsignedInteger('file_size_kb')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 20)->default('active');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_documents');
    }
};
