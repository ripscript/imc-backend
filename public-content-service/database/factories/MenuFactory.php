<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'order' => fake()->numberBetween(1, 100),
            'has_sub' => fake()->boolean(),
            'is_show_front' => fake()->boolean(),
            'is_active' => true,
            'is_dedicated' => false,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
