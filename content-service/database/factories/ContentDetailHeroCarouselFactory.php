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
            'source_type' => null,
            'source_id' => null,
            'button_text' => fake()->word(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
