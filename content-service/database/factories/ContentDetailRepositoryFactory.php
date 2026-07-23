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
            'type' => fake()->randomElement(['pdf', 'doc']),
            'file' => 'uploads/'.fake()->uuid().'.pdf',
            'date' => fake()->date(),
            'is_active' => true,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
