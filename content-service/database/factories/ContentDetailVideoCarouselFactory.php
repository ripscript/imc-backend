<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentDetailVideoCarousel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailVideoCarouselFactory extends Factory
{
    protected $model = ContentDetailVideoCarousel::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'title' => fake()->sentence(4),
            'video_url' => fake()->url(),
            'description' => fake()->paragraph(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
