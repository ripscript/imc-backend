<?php

namespace Database\Factories;

use App\Models\MsSubSatuanKerja;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsSubSatuanKerjaFactory extends Factory
{
    protected $model = MsSubSatuanKerja::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(), 'satuan_kerja_id' => \App\Models\MsSatuanKerja::factory(), 'is_active' => true, 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
