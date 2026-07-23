<?php

namespace Database\Factories;

use App\Models\MsClassificationPemohon;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsClassificationPemohonFactory extends Factory
{
    protected $model = MsClassificationPemohon::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Perorangan', 'Organisasi', 'Lembaga']), 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
