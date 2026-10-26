<?php

namespace Database\Factories;

use App\Models\ContentDetailImageLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailImageLinkFactory extends Factory
{
    protected $model = ContentDetailImageLink::class;

    public function definition(): array
    {
        return [
            'content_id' => 1,
            'title' => fake()->sentence(),
            'image_path' => fake()->filePath(),
            'url' => fake()->url(),
            'caption' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
