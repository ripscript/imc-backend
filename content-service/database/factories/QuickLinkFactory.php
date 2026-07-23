<?php

namespace Database\Factories;

use App\Models\QuickLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuickLinkFactory extends Factory
{
    protected $model = QuickLink::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(2),
            'url' => fake()->url(),
            'is_active' => true,
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
