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
        Schema::create('content_detail_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->text('headline');
            $table->text('description');
            $table->boolean('is_publicated')->default(true);
            $table->text('thumbnail_path')->nullable();
            $table->date('publish_date')->nullable();
            $table->boolean('has_additional_image')->default(false);
            $table->boolean('has_additional_video')->default(false);
            $table->boolean('has_additional_file')->default(false);
            $table->boolean('has_additional_link')->default(false);
            $table->boolean('has_additional_text')->default(false);
            $table->foreignId('layout_id')->nullable()->references('id')->on('ms_layout')->onDelete('cascade');
            $table->boolean('is_slider')->default(false);
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
        Schema::dropIfExists('content_detail_publications');
    }
};
