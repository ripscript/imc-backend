<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_stage_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_stage_id')->constrained('pm_project_stages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_stage_id', 'user_id'], 'uq_stage_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_stage_approvers');
    }
};
