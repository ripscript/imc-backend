<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ms_roles', function (Blueprint $table) {
            if (! Schema::hasColumn('ms_roles', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (! Schema::hasColumn('ms_roles', 'created_by')) {
                $table->string('created_by')->nullable();
            }
            if (! Schema::hasColumn('ms_roles', 'updated_by')) {
                $table->string('updated_by')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ms_roles', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'created_by', 'updated_by']);
        });
    }
};
