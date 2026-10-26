<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_workflow_stage_deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_stage_id')->constrained('pm_workflow_stages')->cascadeOnDelete();
            $table->foreignId('document_type_id')->nullable()->constrained('pm_document_types')->nullOnDelete();
            $table->string('name');
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sequence')->default(1);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_workflow_stage_deliverables');
    }
};
