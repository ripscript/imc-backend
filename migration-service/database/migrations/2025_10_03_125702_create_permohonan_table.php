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
        Schema::create('permohonan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemohon_id')->references('id')->on('pemohon')->onDelete('cascade');
            $table->string('number', 100);
            $table->text('detail');
            $table->text('purpose');
            $table->unsignedBigInteger('destination_institution_id')->nullable();
            $table->string('destination_institution_type')->nullable();
            $table->integer('status');
            $table->text('additional_document_file_path')->nullable();
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
        Schema::dropIfExists('permohonan');
    }
};
