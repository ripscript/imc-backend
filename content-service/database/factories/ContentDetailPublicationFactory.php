<?php

namespace Database\Factories;

use App\Models\ContentDetailPublication;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailPublicationFactory extends Factory
{
    protected $model = ContentDetailPublication::class;

    public function definition(): array
    {
        return [
            'content_id' => \App\Models\Content::factory(),
            'title' => fake()->sentence(4),
            'slug' => fake()->slug(),
            'headline' => fake()->sentence(8),
            'description' => fake()->paragraph(3),
            'is_publicated' => true,
            'thumbnail_path' => 'uploads/'.fake()->uuid().'.jpg',
            'publish_date' => fake()->date(),
            'has_additional_image' => false,
            'has_additional_video' => false,
            'has_additional_file' => false,
            'has_additional_link' => false,
            'has_additional_text' => false,
            'layout_id' => null,
            'is_slider' => false,
            'kategori_content_id' => null,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
