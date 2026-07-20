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
        Schema::table('permohonan_destination_institutions', function (Blueprint $table) {
            $table->boolean('is_need_additional_time')->default(false);
            $table->text('reason_need_additional_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_destination_institutions', function (Blueprint $table) {
            $table->dropColumn('is_need_additional_time');
            $table->dropColumn('reason_need_additional_time');
        });
    }
};
