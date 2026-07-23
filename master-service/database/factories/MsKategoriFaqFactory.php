<?php

namespace Database\Factories;

use App\Models\MsKategoriFaq;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsKategoriFaqFactory extends Factory
{
    protected $model = MsKategoriFaq::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(), 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
