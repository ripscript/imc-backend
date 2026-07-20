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
        Schema::create('survey_kepuasan_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_kepuasan_id')->references('id')->on('survey_kepuasan')->onDelete('cascade');
            $table->foreignId('survey_question_id')->references('id')->on('ms_survey_questions')->onDelete('cascade');
            $table->text('answer')->nullable();
            $table->decimal('bobot', 5, 2)->nullable()->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_kepuasan_answers');
    }
};
