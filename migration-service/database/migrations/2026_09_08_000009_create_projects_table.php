<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_no')->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('workflow_id')->nullable();
            $table->unsignedBigInteger('pic_user_id')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedSmallInteger('progress')->default(0);
            $table->date('start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('pm_clients')->nullOnDelete();
            $table->foreign('partner_id')->references('id')->on('pm_partners')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('pm_project_categories')->nullOnDelete();
            $table->foreign('workflow_id')->references('id')->on('pm_workflows')->nullOnDelete();
            $table->foreign('pic_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
