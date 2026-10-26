<?php

namespace Database\Factories;

use App\Models\ContentDetailAccordionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailAccordionGroupFactory extends Factory
{
    protected $model = ContentDetailAccordionGroup::class;

    public function definition(): array
    {
        return [
            'content_id' => 1,
            'title' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
