<?php

namespace Database\Factories;

use App\Models\ContentDetailImageCarousel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailImageCarouselFactory extends Factory
{
    protected $model = ContentDetailImageCarousel::class;

    public function definition(): array
    {
        return [
            'content_id' => 1,
            'title' => fake()->sentence(),
            'image_path' => fake()->filePath(),
            'caption' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
