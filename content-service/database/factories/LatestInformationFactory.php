<?php

namespace Database\Factories;

use App\Models\LatestInformation;
use Illuminate\Database\Eloquent\Factories\Factory;

class LatestInformationFactory extends Factory
{
    protected $model = LatestInformation::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'link' => fake()->url(),
            'is_active' => true,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
