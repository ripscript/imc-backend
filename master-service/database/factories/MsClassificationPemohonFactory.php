<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MsClassificationPemohonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(),
        ];
    }
}
