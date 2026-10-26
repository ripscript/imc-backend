<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_stage_id')->constrained('pm_project_stages')->cascadeOnDelete();
            $table->foreignId('document_type_id')->nullable()->constrained('pm_document_types')->nullOnDelete();
            $table->string('name');
            $table->boolean('is_required')->default(true);
            $table->string('status', 20)->default('pending');
            $table->date('due_date')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_deliverables');
    }
};
