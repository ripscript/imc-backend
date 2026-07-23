<?php

namespace Database\Factories;

use App\Models\MsHoliday;
use Illuminate\Database\Eloquent\Factories\Factory;

class MsHolidayFactory extends Factory
{
    protected $model = MsHoliday::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3), 'date' => fake()->date(), 'created_by' => 'factory', 'updated_by' => 'factory',
        ];
    }
}
