<?php

namespace Database\Factories;

use App\Models\ProjectStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectStageFactory extends Factory
{
    protected $model = ProjectStage::class;

    public function definition(): array
    {
        return [
            'project_id' => 1,
            'workflow_stage_id' => null,
            'name' => fake()->words(2, true),
            'sequence' => 1,
            'status' => 'pending',
            'progress' => 0,
            'is_required' => true,
            'allow_skip' => false,
            'allow_repeat' => false,
            'requires_approval' => false,
            'is_configured' => false,
        ];
    }
}
