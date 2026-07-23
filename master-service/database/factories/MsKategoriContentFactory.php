<?php

namespace Database\Factories;

use App\Models\MsKategoriContent;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsKategoriContentFactory extends Factory
{
    protected $model = MsKategoriContent::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(), 'is_active' => true, 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
