<?php

namespace Database\Factories;

use App\Models\ChatSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatSessionFactory extends Factory
{
    protected $model = ChatSession::class;

    public function definition(): array
    {
        return [
            'session_id' => fake()->unique()->uuid(), 'user_agent' => fake()->userAgent(), 'ip_address' => fake()->ipv4(),
        ];
    }
}
