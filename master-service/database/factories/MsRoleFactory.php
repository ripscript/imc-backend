<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MsRoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'code' => fake()->unique()->slug(),
        ];
    }
}
