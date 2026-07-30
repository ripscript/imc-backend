<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentDetailImageCarousel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailImageCarouselFactory extends Factory
{
    protected $model = ContentDetailImageCarousel::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'image_url' => fake()->imageUrl(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
