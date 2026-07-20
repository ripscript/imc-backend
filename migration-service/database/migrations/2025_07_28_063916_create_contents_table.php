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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->morphs('parent');
            $table->string('type', 30);
            $table->boolean('is_section')->default(false);
            $table->string('section_title')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('has_additional_image')->default(false);
            $table->boolean('has_additional_video')->default(false);
            $table->boolean('has_additional_file')->default(false);
            $table->boolean('has_additional_link')->default(false);
            $table->boolean('has_additional_text')->default(false);
            $table->foreignId('layout_id')->nullable()->references('id')->on('ms_layout')->onDelete('cascade');
            $table->boolean('is_show_home')->default(false);
            $table->boolean('is_show_tag')->default(false);
            $table->boolean('is_accessible')->default(true);
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
        Schema::dropIfExists('contents');
    }
};
