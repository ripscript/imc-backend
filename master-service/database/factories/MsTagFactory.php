<?php

namespace Database\Factories;

use App\Models\MsTag;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsTagFactory extends Factory
{
    protected $model = MsTag::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(), 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
