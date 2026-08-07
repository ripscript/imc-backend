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
            'uuid' => fake()->uuid(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'kategori_faq_id' => 1,
            'is_active' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
