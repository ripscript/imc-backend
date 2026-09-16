<?php

namespace Database\Factories;

use App\Models\ContentDetailRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailRepositoryFactory extends Factory
{
    protected $model = ContentDetailRepository::class;

    public function definition(): array
    {
        return [
            'content_id' => \App\Models\Content::factory(),
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'file_path' => 'uploads/'.fake()->uuid().'.pdf',
            'thumbnail_path' => 'uploads/'.fake()->uuid().'.jpg',
            'publish_date' => fake()->dateTime(),
            'is_publicated' => true,
            'layout_id' => null,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
