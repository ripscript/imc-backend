<?php

namespace Database\Factories;

use App\Models\PmPartner;
use Illuminate\Database\Eloquent\Factories\Factory;

class PmPartnerFactory extends Factory
{
    protected $model = PmPartner::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'identifier' => 'PR-'.fake()->unique()->numberBetween(1000, 9999),
            'status' => 'active',
        ];
    }
}
