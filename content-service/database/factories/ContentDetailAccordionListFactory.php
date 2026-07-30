<?php

namespace Database\Factories;

use App\Models\ContentDetailAccordionGroup;
use App\Models\ContentDetailAccordionList;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailAccordionListFactory extends Factory
{
    protected $model = ContentDetailAccordionList::class;

    public function definition(): array
    {
        return [
            'accordion_group_id' => ContentDetailAccordionGroup::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
