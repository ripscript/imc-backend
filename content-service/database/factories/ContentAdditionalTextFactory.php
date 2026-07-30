<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentAdditionalText;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentAdditionalTextFactory extends Factory
{
    protected $model = ContentAdditionalText::class;

    public function definition(): array
    {
        return [
            'content_type' => Content::class,
            'content_id' => Content::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
