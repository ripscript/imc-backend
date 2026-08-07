<?php

namespace Database\Factories;

use App\Models\ContentDetailOrganizationalStructureMember;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailOrganizationalStructureMemberFactory extends Factory
{
    protected $model = ContentDetailOrganizationalStructureMember::class;

    public function definition(): array
    {
        return [
            'content_detail_organizational_structure_group_id' => 1,
            'parent_id' => null,
            'name' => fake()->name(),
            'position' => fake()->jobTitle(),
            'image_path' => fake()->filePath(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
