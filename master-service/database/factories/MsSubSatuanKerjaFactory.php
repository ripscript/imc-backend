<?php

namespace Database\Factories;

use App\Models\MsSatuanKerja;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsSubSatuanKerjaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'satuan_kerja_id' => MsSatuanKerja::factory(),
        ];
    }
}
