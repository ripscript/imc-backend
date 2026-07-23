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
            'type' => fake()->randomElement(['pdf', 'doc', 'xls']),
            'file' => 'uploads/'.fake()->uuid().'.pdf',
            'date' => fake()->date(),
            'is_active' => true,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
