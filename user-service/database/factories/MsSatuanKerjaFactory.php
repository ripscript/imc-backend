<?php

namespace Database\Factories;

use App\Models\MsSatuanKerja;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsSatuanKerjaFactory extends Factory
{
    protected $model = MsSatuanKerja::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(), 'is_active' => true, 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
