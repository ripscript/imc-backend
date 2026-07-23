<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        $title = fake()->unique()->randomElement(['Beranda', 'Profil', 'Layanan', 'Informasi Publik', 'Galeri']);

        return [
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'order' => fake()->numberBetween(1, 20),
            'has_sub' => false,
            'is_active' => true,
            'is_dedicated' => false,
            'is_show_front' => fake()->boolean(),
            'is_show_side_menu' => false,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
