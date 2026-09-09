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
            'name' => fake()->unique()->company(),
            'identifier' => 'PR-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => 'active',
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
