<?php

namespace Database\Factories;

use App\Models\PmWorkflowStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class PmWorkflowStageFactory extends Factory
{
    protected $model = PmWorkflowStage::class;

    public function definition(): array
    {
        return [
            'workflow_id' => PmWorkflowFactory::new(),
            'code' => 'WS-'.fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->words(2, true),
            'sequence' => 1,
            'is_required' => true,
            'allow_skip' => false,
            'allow_repeat' => false,
        ];
    }
}
