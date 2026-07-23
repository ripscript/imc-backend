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
            'parent_type' => \App\Models\Menu::class,
            'parent_id' => \App\Models\Menu::factory(),
            'type' => fake()->randomElement(['text', 'image_carousel', 'card_link', 'video_carousel']),
            'section_title' => fake()->sentence(2),
            'is_section' => false,
            'order' => fake()->numberBetween(1, 10),
            'layout_id' => 1,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
