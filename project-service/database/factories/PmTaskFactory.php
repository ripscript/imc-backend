<?php

namespace Database\Factories;

use App\Models\PmTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class PmTaskFactory extends Factory
{
    protected $model = PmTask::class;

    public function definition(): array
    {
        return [
            'project_stage_id' => 1,
            'title' => fake()->sentence(3),
            'status' => 'pending',
            'priority' => 'medium',
        ];
    }
}
