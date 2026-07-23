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
            'type' => fake()->unique()->randomElement(['landing_page', 'internal_page', 'public_page']),
            'name' => fake()->sentence(2),
        ];
    }
}
