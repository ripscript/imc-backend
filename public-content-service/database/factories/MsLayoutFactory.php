<?php

namespace Database\Factories;

use App\Models\MsLayout;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsLayoutFactory extends Factory
{
    protected $model = MsLayout::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
