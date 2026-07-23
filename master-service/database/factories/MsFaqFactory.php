<?php

namespace Database\Factories;

use App\Models\MsFaq;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsFaqFactory extends Factory
{
    protected $model = MsFaq::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(5), 'is_active' => true, 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
