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
            'pemohon_id' => null,
            'last_activity_at' => now(),
            'started_at' => now(),
            'ended_at' => null,
            'is_active' => true,
        ];
    }
}
