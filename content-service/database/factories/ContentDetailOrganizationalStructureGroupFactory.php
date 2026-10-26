<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentDetailOrganizationalStructureGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailOrganizationalStructureGroupFactory extends Factory
{
    protected $model = ContentDetailOrganizationalStructureGroup::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'title' => fake()->sentence(4),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
