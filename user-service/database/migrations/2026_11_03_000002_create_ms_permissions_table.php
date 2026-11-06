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
        if (! Schema::hasTable('ms_permissions')) {
            Schema::create('ms_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('action', 100);
                $table->string('code', 200)->unique();
                $table->string('label', 200);
                $table->string('group', 100)->nullable();
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_permissions');
    }
};
