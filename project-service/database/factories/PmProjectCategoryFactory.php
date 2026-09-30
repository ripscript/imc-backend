<?php

namespace Database\Factories;

use App\Models\PmProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class PmProjectCategoryFactory extends Factory
{
    protected $model = PmProjectCategory::class;

    public function definition(): array
    {
        return [
            'code' => 'PC-'.fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->words(2, true),
            'status' => 'active',
        ];
    }
}
