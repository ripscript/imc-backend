<?php

namespace Database\Factories;

use App\Models\MsNegara;
use App\Models\MsPerwakilan;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsPerwakilanNegaraFactory extends Factory
{
    public function definition(): array
    {
        return [
            'perwakilan_id' => MsPerwakilan::factory(),
            'negara_id' => MsNegara::factory(),
        ];
    }
}
