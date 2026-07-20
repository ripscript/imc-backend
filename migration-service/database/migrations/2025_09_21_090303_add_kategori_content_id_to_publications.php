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
        Schema::table('content_detail_publications', function (Blueprint $table) {
            $table->foreignId('kategori_content_id')->nullable()->references('id')->on('ms_kategori_contents')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_detail_publications', function (Blueprint $table) {
            //
        });
    }
};
