<?php

namespace Database\Factories;

use App\Models\MsAlasanKeberatan;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsAlasanKeberatanFactory extends Factory
{
    protected $model = MsAlasanKeberatan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(4), 'is_active' => true, 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
