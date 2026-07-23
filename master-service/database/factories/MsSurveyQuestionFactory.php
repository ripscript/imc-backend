<?php

namespace Database\Factories;

use App\Models\MsSurveyQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsSurveyQuestionFactory extends Factory
{
    protected $model = MsSurveyQuestion::class;

    public function definition(): array
    {
        return [
            'question' => fake()->sentence(8).'?', 'is_active' => true, 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
