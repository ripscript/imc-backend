<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentDetailConsume;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailConsumeFactory extends Factory
{
    protected $model = ContentDetailConsume::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'type' => fake()->randomElement(['file', 'image', 'link']),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'file_url' => fake()->url(),
            'image_url' => fake()->imageUrl(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
