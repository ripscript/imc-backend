<?php

namespace Database\Factories;

use App\Models\MsSifatInformasi;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsSifatInformasiFactory extends Factory
{
    protected $model = MsSifatInformasi::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Terbuka', 'Terbatas', 'Rahasia']), 'is_active' => true, 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
