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
        if (DB::getDriverName() === 'sqlite') {
            Schema::dropIfExists('content_detail_accordion_groups');
            Schema::create('content_detail_accordion_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('title');
                $table->integer('order');
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
            return;
        }

        Schema::table('content_detail_accordion_groups', function (Blueprint $table) {
            $table->dropForeign(['content_detail_accordion_id']);
            $table->dropColumn('content_detail_accordion_id');
        });

        Schema::table('content_detail_accordion_groups', function (Blueprint $table) {
            $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_detail_accordion_groups', function (Blueprint $table) {
            $table->dropForeign(['content_id']);
            $table->dropColumn('content_id');
        });

        Schema::table('content_detail_accordion_groups', function (Blueprint $table) {
            $table->foreignId('content_detail_accordion_id')
                ->constrained('content_detail_accordions')
                ->onDelete('cascade');
        });
    }
};
