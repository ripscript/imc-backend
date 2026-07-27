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
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'is_active' => fake()->boolean(),
            'kategori_faq_id' => 1,
        ];
    }
}
