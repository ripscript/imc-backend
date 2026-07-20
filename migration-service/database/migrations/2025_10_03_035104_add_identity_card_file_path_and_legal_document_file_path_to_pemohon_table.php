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
            $table->text('identity_card_file_path')->nullable();
            $table->text('legal_document_file_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemohon', function (Blueprint $table) {
            $table->dropColumn('identity_card_file_path');
            $table->dropColumn('legal_document_file_path');
        });
    }
};
