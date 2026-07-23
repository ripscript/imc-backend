<?php

namespace Database\Factories;

use App\Models\MsRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsRoleFactory extends Factory
{
    protected $model = MsRole::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Superadmin', 'Admin Pusat', 'Admin Pustik', 'Admin Satker', 'Pimpinan']), 'slug' => fake()->unique()->word(), 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
