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
            $table->integer('status')->default(0);
            $table->dateTime('due_date_response')->nullable();
            $table->boolean('is_late_response')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_destination_institutions', function (Blueprint $table) {
            //
        });
    }
};
