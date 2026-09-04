<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentDetailHeroCarousel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailHeroCarouselFactory extends Factory
{
    protected $model = ContentDetailHeroCarousel::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'image_path' => fake()->imageUrl(),
            'tag' => fake()->word(),
            'title' => fake()->sentence(3),
            'subtitle' => fake()->paragraph(1),
            'button_text' => fake()->word(),
            'button_link' => fake()->url(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
