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
            'uuid' => fake()->uuid(),
            'name' => fake()->words(2, true),
            'is_active' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
