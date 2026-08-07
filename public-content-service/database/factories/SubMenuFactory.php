<?php

namespace Database\Factories;

use App\Models\SubMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubMenuFactory extends Factory
{
    protected $model = SubMenu::class;

    public function definition(): array
    {
        return [
            'menu_id' => 1,
            'title' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'order' => fake()->numberBetween(1, 100),
            'has_child' => fake()->boolean(),
            'is_active' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
