<?php

namespace Database\Factories;

use App\Models\LatestInformation;
use Illuminate\Database\Eloquent\Factories\Factory;

class LatestInformationFactory extends Factory
{
    protected $model = LatestInformation::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'url' => fake()->url(),
            'publish_date' => fake()->date(),
            'is_active' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
