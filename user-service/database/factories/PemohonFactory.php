<?php

namespace Database\Factories;

use App\Models\Pemohon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PemohonFactory extends Factory
{
    protected $model = Pemohon::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(), 'username' => fake()->unique()->userName(), 'email' => fake()->unique()->safeEmail(), 'password' => bcrypt('password'), 'status' => 0, 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
