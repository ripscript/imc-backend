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
            'menu_id' => \App\Models\Menu::factory(),
            'is_active' => true,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
