<?php

namespace Database\Factories;

use App\Models\PmWorkflowStage;
use App\Models\PmWorkflowStageDeliverable;
use Illuminate\Database\Eloquent\Factories\Factory;

class PmWorkflowStageDeliverableFactory extends Factory
{
    protected $model = PmWorkflowStageDeliverable::class;

    public function definition(): array
    {
        return [
            'workflow_stage_id' => PmWorkflowStage::factory(),
            'document_type_id' => null,
            'name' => fake()->words(2, true),
            'is_required' => true,
            'sequence' => 1,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
