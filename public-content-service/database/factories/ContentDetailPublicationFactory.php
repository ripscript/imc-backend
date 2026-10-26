<?php

namespace Database\Factories;

use App\Models\ContentDetailPublication;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailPublicationFactory extends Factory
{
    protected $model = ContentDetailPublication::class;

    public function definition(): array
    {
        return [
            'content_id' => 1,
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'type' => 'publication',
            'is_publicated' => true,
            'layout_id' => null,
            'kategori_content_id' => null,
            'publish_date' => fake()->date(),
            'thumbnail_path' => fake()->filePath(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
