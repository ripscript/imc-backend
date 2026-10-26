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
            'content_id' => 1,
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'type' => 'gallery',
            'sub_type' => null,
            'is_publicated' => true,
            'layout_id' => null,
            'publish_date' => fake()->date(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
