<?php

namespace Database\Factories;

use App\Models\PmWorkflow;
use Illuminate\Database\Eloquent\Factories\Factory;

class PmWorkflowFactory extends Factory
{
    protected $model = PmWorkflow::class;

    public function definition(): array
    {
        return [
            'code' => 'WF-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'name' => fake()->unique()->words(3, true),
            'version' => 1,
            'status' => 'active',
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
