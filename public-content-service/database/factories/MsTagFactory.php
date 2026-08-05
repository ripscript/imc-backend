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
            'title' => fake()->word(),
            'slug' => fake()->unique()->slug(),
            'is_active' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
