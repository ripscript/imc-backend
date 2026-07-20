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
        Schema::create('content_detail_gallery_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_detail_gallery_id')->references('id')->on('content_detail_galleries')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->text('image_path')->nullable();
            $table->string('video_type', 20)->nullable();
            $table->text('video_path')->nullable();
            $table->text('video_url')->nullable();        
            $table->integer('order');
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
        Schema::dropIfExists('content_detail_gallery_lists');
    }
};
