<?php

namespace Database\Factories;

use App\Models\ContentDetailOrganizationalStructureGroup;
use App\Models\ContentDetailOrganizationalStructureMember;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailOrganizationalStructureMemberFactory extends Factory
{
    protected $model = ContentDetailOrganizationalStructureMember::class;

    public function definition(): array
    {
        return [
            'group_id' => ContentDetailOrganizationalStructureGroup::factory(),
            'name' => fake()->name(),
            'position' => fake()->jobTitle(),
            'image_url' => fake()->imageUrl(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
