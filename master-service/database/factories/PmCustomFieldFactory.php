<?php

namespace Database\Factories;

use App\Models\PmCustomField;
use Illuminate\Database\Eloquent\Factories\Factory;

class PmCustomFieldFactory extends Factory
{
    protected $model = PmCustomField::class;

    public function definition(): array
    {
        return [
            'code' => 'cf_project_'.fake()->unique()->numberBetween(1, 9999),
            'name' => fake()->unique()->words(2, true),
            'entity_type' => 'project',
            'field_type' => fake()->randomElement(['text', 'number', 'money']),
            'is_required' => false,
            'status' => 'active',
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
