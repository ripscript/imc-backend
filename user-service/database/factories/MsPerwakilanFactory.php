<?php

namespace Database\Factories;

use App\Models\MsPerwakilan;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsPerwakilanFactory extends Factory
{
    protected $model = MsPerwakilan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city().' Embassy', 'type' => fake()->randomElement(['Kedutaan', 'KJRI', 'KBRI']), 'negara_id' => 1, 'address' => fake()->address(), 'phone' => fake()->phoneNumber(), 'email' => fake()->companyEmail(), 'website' => fake()->url(), 'is_active' => true, 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
