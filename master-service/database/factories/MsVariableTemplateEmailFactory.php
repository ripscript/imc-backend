<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MsVariableTemplateEmailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'is_active' => fake()->boolean(),
            'created_by' => fake()->name(),
            'updated_by' => fake()->name(),
        ];
    }
}
