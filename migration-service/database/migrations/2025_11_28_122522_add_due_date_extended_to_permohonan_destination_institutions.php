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
            $table->dateTime('due_date_extended')->nullable();
            $table->integer('additional_time_days')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_destination_institutions', function (Blueprint $table) {
            $table->dropColumn('due_date_extended');
            $table->dropColumn('additional_time_days');
        });
    }
};
