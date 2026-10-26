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
            'code' => 'WF-'.fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->words(3, true),
            'version' => 1,
            'status' => 'active',
        ];
    }
}
