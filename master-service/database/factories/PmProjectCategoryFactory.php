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
            'code' => 'PC-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'name' => fake()->unique()->words(3, true),
            'status' => 'active',
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
