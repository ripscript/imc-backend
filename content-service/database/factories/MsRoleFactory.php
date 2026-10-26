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
            'name' => fake()->randomElement(['Super Admin', 'Admin', 'User', 'Editor']),
            'code' => fake()->unique()->randomElement(['super_admin', 'admin', 'user', 'editor']),
            'is_active' => true,
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
