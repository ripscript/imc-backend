<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MsPermohonanFormatDataFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
        ];
    }
}
