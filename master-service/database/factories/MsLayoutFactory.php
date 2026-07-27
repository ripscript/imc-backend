<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MsLayoutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'is_custom' => fake()->boolean(),
            'type' => fake()->randomElement(['default', 'custom']),
        ];
    }
}
