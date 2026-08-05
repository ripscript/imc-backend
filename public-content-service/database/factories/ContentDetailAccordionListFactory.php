<?php

namespace Database\Factories;

use App\Models\ContentDetailAccordionList;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailAccordionListFactory extends Factory
{
    protected $model = ContentDetailAccordionList::class;

    public function definition(): array
    {
        return [
            'content_detail_accordion_group_id' => 1,
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
