<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_detail_projects', function (Blueprint $table) {
            $table->foreignId('activation_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('activation_requested_at')->nullable();
            $table->text('rejection_notes')->nullable();
        });

        Schema::table('pm_project_stages', function (Blueprint $table) {
            $table->foreignId('clearance_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('clearance_requested_at')->nullable();
            $table->text('rejection_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('content_detail_projects', function (Blueprint $table) {
            $table->dropForeign(['activation_requested_by']);
            $table->dropColumn(['activation_requested_by', 'activation_requested_at', 'rejection_notes']);
        });

        Schema::table('pm_project_stages', function (Blueprint $table) {
            $table->dropForeign(['clearance_requested_by']);
            $table->dropColumn(['clearance_requested_by', 'clearance_requested_at', 'rejection_notes']);
        });
    }
};
