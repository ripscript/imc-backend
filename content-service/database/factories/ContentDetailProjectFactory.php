<?php

namespace Database\Factories;

use App\Models\ContentDetailProject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailProjectFactory extends Factory
{
    protected $model = ContentDetailProject::class;

    public function definition(): array
    {
        return [
            'content_id' => \App\Models\Content::factory(),
            'title' => fake()->sentence(4),
            'slug' => fake()->slug(),
            'description' => fake()->paragraph(),
            'thumbnail_path' => 'uploads/'.fake()->uuid().'.jpg',
            'is_publicated' => true,
            'publish_date' => fake()->date(),
            'duration' => fake()->randomElement(['3 bulan', '6 bulan', '1 tahun', null]),
            'location' => fake()->city(),
            'start_date' => fake()->date(),
            'budget' => fake()->randomElement([100000000, 250000000, 500000000, null]),
            'organization_address' => fake()->address(),
            'organization_name' => fake()->company(),
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
