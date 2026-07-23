<?php

namespace Database\Factories;

use App\Models\ChatLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatLogFactory extends Factory
{
    protected $model = ChatLog::class;

    public function definition(): array
    {
        return [
            'session_id' => fake()->uuid(), 'question' => fake()->sentence(), 'answer' => fake()->sentence(), 'created_by' => 'factory',
        ];
    }
}
