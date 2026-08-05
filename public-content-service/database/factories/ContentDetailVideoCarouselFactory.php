<?php

namespace Database\Factories;

use App\Models\ContentDetailVideoCarousel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailVideoCarouselFactory extends Factory
{
    protected $model = ContentDetailVideoCarousel::class;

    public function definition(): array
    {
        return [
            'content_id' => 1,
            'title' => fake()->sentence(),
            'video_url' => fake()->url(),
            'thumbnail_path' => fake()->filePath(),
            'caption' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
