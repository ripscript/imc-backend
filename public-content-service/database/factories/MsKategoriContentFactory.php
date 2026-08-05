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
            'name' => fake()->words(2, true),
            'title' => fake()->words(3, true),
            'is_active' => true,
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
