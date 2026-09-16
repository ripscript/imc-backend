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
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'order' => fake()->numberBetween(1, 10),
            'menu_id' => \App\Models\Menu::factory(),
            'has_child' => false,
            'is_active' => true,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
