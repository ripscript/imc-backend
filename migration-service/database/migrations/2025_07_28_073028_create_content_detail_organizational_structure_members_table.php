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
        Schema::create('content_detail_organizational_structure_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_detail_organizational_structure_group_id')->references('id')->on('content_detail_organizational_structure_groups')->onDelete('cascade');
            $table->string('name');
            $table->integer('level');
            $table->string('position');
            $table->text('description')->nullable();
            $table->text('image_path')->nullable();
            $table->boolean('is_child')->default(false);
            $table->boolean('have_child')->default(false);
            $table->foreignId('parent_id')->nullable()->references('id')->on('content_detail_organizational_structure_members')->onDelete('cascade');
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
        Schema::dropIfExists('content_detail_organizational_structure_members');
    }
};
