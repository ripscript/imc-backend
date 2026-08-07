<?php

namespace Database\Factories;

use App\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentFactory extends Factory
{
    protected $model = Content::class;

    public function definition(): array
    {
        return [
            'layout_id' => null,
            'parent_type' => 'App\Models\Menu',
            'parent_id' => 1,
            'type' => fake()->randomElement(['publication', 'repository', 'gallery', 'text', 'card_link', 'image_carousel', 'image_link', 'video_carousel', 'accordion', 'consume', 'agenda']),
            'sub_type' => null,
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'section_title' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 100),
            'is_section' => fake()->boolean(),
            'is_accessible' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
