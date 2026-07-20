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
        Schema::create('permohonan_format_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->references('id')->on('permohonan')->onDelete('cascade');
            $table->foreignId('format_data_id')->references('id')->on('ms_permohonan_format_data')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan_format_data');
    }
};
