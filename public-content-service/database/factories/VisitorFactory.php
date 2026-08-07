<?php

namespace Database\Factories;

use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorFactory extends Factory
{
    protected $model = Visitor::class;

    public function definition(): array
    {
        return [
            'ip_address' => fake()->ipv4(),
            'slug' => fake()->slug(),
            'latitude' => fake()->latitude(-90, 90),
            'longitude' => fake()->longitude(-180, 180),
            'country' => fake()->country(),
            'city' => fake()->city(),
            'user_agent' => fake()->userAgent(),
            'device_type' => fake()->randomElement(['mobile', 'desktop']),
        ];
    }
}
