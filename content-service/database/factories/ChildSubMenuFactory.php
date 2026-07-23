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
            'title' => fake()->sentence(3),
            'sub_menu_id' => \App\Models\SubMenu::factory(),
            'is_active' => true,
            'created_by' => 'factory',
            'updated_by' => 'factory'
        ];
    }
}
