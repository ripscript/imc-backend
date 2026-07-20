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
        Schema::table('global_search_index', function (Blueprint $table) {
            DB::statement("
                CREATE EXTENSION IF NOT EXISTS pg_trgm;
            ");

            DB::statement('
                CREATE INDEX gsi_search_vector_idx
                ON global_search_index
                USING GIN(search_vector)
            ');

            DB::statement('
                CREATE INDEX gsi_title_trgm_idx
                ON global_search_index
                USING GIN(title gin_trgm_ops)
            ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_search_index', function (Blueprint $table) {
            //
        });
    }
};
