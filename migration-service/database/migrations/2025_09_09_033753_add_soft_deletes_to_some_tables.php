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
        Schema::table('ms_negara', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('ms_perwakilan', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('ms_sifat_informasi', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('ms_kategori_faq', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('ms_faq', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
