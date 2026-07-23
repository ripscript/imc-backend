<?php

namespace Database\Factories;

use App\Models\ContentDetailAgenda;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailAgendaFactory extends Factory
{
    protected $model = ContentDetailAgenda::class;

    public function definition(): array
    {
        return [
            'content_id' => \App\Models\Content::factory(),
            'title' => fake()->sentence(4),
            'type' => fake()->randomElement(['event', 'meeting']),
            'description' => fake()->paragraph(),
            'date_start' => fake()->dateTimeBetween('+1 days', '+1 month'),
            'date_end' => fake()->dateTimeBetween('+1 month', '+2 months'),
            'is_active' => true,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
