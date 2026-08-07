<?php

namespace Database\Factories;

use App\Models\ContentDetailAgenda;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailAgendaFactory extends Factory
{
    protected $model = ContentDetailAgenda::class;

    public function definition(): array
    {
        return [
            'content_id' => 1,
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'datetime_start' => fake()->dateTime(),
            'datetime_end' => fake()->dateTime('+1 hour'),
            'is_active' => true,
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
