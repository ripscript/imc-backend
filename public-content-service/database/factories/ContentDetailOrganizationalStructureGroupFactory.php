<?php

namespace Database\Factories;

use App\Models\ContentDetailOrganizationalStructureGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailOrganizationalStructureGroupFactory extends Factory
{
    protected $model = ContentDetailOrganizationalStructureGroup::class;

    public function definition(): array
    {
        return [
            'content_id' => 1,
            'name' => fake()->words(2, true),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
