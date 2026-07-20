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
        Schema::table('permohonan_tanggapan', function (Blueprint $table) {
            $table->foreignId('permohonan_destination_institution_id')->nullable()->references('id')->on('permohonan_destination_institutions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_tanggapan', function (Blueprint $table) {
            //
        });
    }
};
