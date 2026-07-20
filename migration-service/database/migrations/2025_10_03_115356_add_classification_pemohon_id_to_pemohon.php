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
        Schema::table('pemohon', function (Blueprint $table) {
            $table->dropColumn('classification');
        });

        Schema::table('pemohon', function (Blueprint $table) {
            $table->foreignId('classification_pemohon_id')->nullable()->references('id')->on('ms_classification_pemohon')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemohon', function (Blueprint $table) {
            //
        });
    }
};
