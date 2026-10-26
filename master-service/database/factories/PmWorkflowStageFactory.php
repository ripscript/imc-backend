<?php

namespace Database\Factories;

use App\Models\PmWorkflow;
use App\Models\PmWorkflowStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class PmWorkflowStageFactory extends Factory
{
    protected $model = PmWorkflowStage::class;

    public function definition(): array
    {
        return [
            'workflow_id' => PmWorkflow::factory(),
            'code' => 'WS-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'name' => fake()->unique()->word(),
            'sequence' => fake()->unique()->numberBetween(1, 50),
            'is_required' => true,
            'allow_skip' => false,
            'allow_repeat' => false,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
