<?php

namespace Database\Factories;

use App\Models\PmWorkflowStage;
use App\Models\PmWorkflowStageTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class PmWorkflowStageTaskFactory extends Factory
{
    protected $model = PmWorkflowStageTask::class;

    public function definition(): array
    {
        return [
            'workflow_stage_id' => PmWorkflowStage::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'priority' => 'medium',
            'sequence' => 1,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
