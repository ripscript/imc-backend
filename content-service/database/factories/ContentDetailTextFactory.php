<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentDetailText;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailTextFactory extends Factory
{
    protected $model = ContentDetailText::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'type' => fake()->randomElement(['text']),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
