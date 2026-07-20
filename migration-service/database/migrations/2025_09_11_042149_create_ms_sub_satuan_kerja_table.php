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
        Schema::create('ms_sub_satuan_kerja', function (Blueprint $table) {
            $table->softDeletes();
            $table->id();
            $table->string('name');
            $table->foreignId('satuan_kerja_id')->references('id')->on('ms_satuan_kerja')->onDelete('cascade');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_sub_satuan_kerja');
    }
};
