<?php

namespace Database\Factories;

use App\Models\ContentDetailCardLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailCardLinkFactory extends Factory
{
    protected $model = ContentDetailCardLink::class;

    public function definition(): array
    {
        return [
            'content_id' => 1,
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'image_path' => fake()->filePath(),
            'url' => fake()->url(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
