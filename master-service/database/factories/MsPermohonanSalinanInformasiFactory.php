<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MsPermohonanSalinanInformasiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
        ];
    }
}
