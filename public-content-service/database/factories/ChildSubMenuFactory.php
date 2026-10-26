<?php

namespace Database\Factories;

use App\Models\ChildSubMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChildSubMenuFactory extends Factory
{
    protected $model = ChildSubMenu::class;

    public function definition(): array
    {
        return [
            'sub_menu_id' => 1,
            'title' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'order' => fake()->numberBetween(1, 100),
            'is_active' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
