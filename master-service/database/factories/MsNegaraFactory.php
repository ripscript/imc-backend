<?php

namespace Database\Factories;

use App\Models\MsNegara;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsNegaraFactory extends Factory
{
    protected $model = MsNegara::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->country(), 'kode_telepon' => fake()->numerify('+###'), 'kode_negara' => fake()->unique()->countryCode(), 'is_active' => true, 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
