<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('pm_workflows')->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->unsignedInteger('sequence');
            $table->boolean('is_required')->default(true);
            $table->boolean('allow_skip')->default(false);
            $table->boolean('allow_repeat')->default(false);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['workflow_id', 'sequence']);
            $table->unique(['workflow_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_workflow_stages');
    }
};
