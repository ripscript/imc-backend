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
            'chat_session_id' => 1,
            'sender' => fake()->randomElement(['user', 'bot']),
            'message' => fake()->sentence(),
        ];
    }
}
