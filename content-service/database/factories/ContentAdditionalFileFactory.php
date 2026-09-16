<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentAdditionalFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentAdditionalFileFactory extends Factory
{
    protected $model = ContentAdditionalFile::class;

    public function definition(): array
    {
        return [
            'content_type' => Content::class,
            'content_id' => Content::factory(),
            'title' => fake()->sentence(4),
            'file_path' => 'uploads/'.fake()->uuid().'.pdf',
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
