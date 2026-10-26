<?php

namespace Database\Factories;

use App\Models\PmClient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PmClientFactory extends Factory
{
    protected $model = PmClient::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'identifier' => 'CL-'.fake()->unique()->numberBetween(1000, 9999),
            'status' => 'active',
        ];
    }
}
