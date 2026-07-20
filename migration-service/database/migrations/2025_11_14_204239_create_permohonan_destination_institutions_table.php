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
        Schema::create('permohonan_destination_institutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->references('id')->on('permohonan')->onDelete('cascade');
            $table->unsignedBigInteger('destination_institution_id')->nullable();
            $table->string('destination_institution_type')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan_destination_institutions');
    }
};
