<?php

namespace Database\Factories;

use App\Models\ContentDetailConsume;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailConsumeFactory extends Factory
{
    protected $model = ContentDetailConsume::class;

    public function definition(): array
    {
        return [
            'content_id' => 1,
            'consume_id' => 1,
            'consume_type' => fake()->randomElement(['publication', 'repository']),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
