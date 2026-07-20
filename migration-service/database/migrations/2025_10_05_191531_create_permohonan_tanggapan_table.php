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
        Schema::create('permohonan_tanggapan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->references('id')->on('permohonan')->onDelete('cascade');
            $table->string('number', 100)->nullable();
            $table->text('fpt_file_path')->nullable();
            $table->text('description')->nullable();
            $table->text('attachment_file_path')->nullable();
            $table->text('reason_rejected')->nullable();
            $table->text('rejection_attachment_file_path')->nullable();
            $table->string('verified_by')->nullable();
            $table->string('rejected_by')->nullable();
            $table->string('answered_by')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->dateTime('answered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan_tanggapan');
    }
};
