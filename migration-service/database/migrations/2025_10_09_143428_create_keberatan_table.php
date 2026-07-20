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
        Schema::create('keberatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->references('id')->on('permohonan')->onDelete('cascade');
            $table->foreignId('pemohon_id')->references('id')->on('pemohon')->onDelete('cascade');
            $table->text('detail');
            $table->text('additional_document_file_path')->nullable();
            $table->integer('status');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keberatan');
    }
};
