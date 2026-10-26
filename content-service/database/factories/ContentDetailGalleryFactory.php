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
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'is_publicated' => true,
            'publish_date' => fake()->dateTime(),
            'layout_id' => null,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
