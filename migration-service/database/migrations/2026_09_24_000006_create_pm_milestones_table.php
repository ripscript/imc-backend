<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('content_detail_projects')->cascadeOnDelete();
            $table->string('name');
            $table->date('target_date');
            $table->string('status', 20)->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_milestones');
    }
};
