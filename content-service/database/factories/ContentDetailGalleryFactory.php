<?php

namespace Database\Factories;

use App\Models\ContentDetailGallery;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailGalleryFactory extends Factory
{
    protected $model = ContentDetailGallery::class;

    public function definition(): array
    {
        return [
            'content_id' => \App\Models\Content::factory(),
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(['image', 'video']),
            'description' => fake()->paragraph(),
            'date' => fake()->date(),
            'is_active' => true,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
