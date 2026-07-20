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
        Schema::create('keberatan_alasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keberatan_id')->references('id')->on('keberatan')->onDelete('cascade');
            $table->foreignId('alasan_keberatan_id')->references('id')->on('ms_alasan_keberatan')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keberatan_alasan');
    }
};
