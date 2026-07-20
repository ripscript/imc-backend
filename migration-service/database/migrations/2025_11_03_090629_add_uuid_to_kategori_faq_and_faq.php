<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ms_kategori_faq', function (Blueprint $table) {
            $table->uuid('uuid')->default(DB::raw('gen_random_uuid()'));
        });

        Schema::table('ms_faq', function (Blueprint $table) {
            $table->uuid('uuid')->default(DB::raw('gen_random_uuid()'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ms_kategori_faq', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('ms_faq', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
