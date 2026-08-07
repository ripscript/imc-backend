<?php

namespace Database\Factories;

use App\Models\ContentAdditionalFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentAdditionalFileFactory extends Factory
{
    protected $model = ContentAdditionalFile::class;

    public function definition(): array
    {
        return [
            'content_type' => 'App\Models\Content',
            'content_id' => 1,
            'file_path' => fake()->filePath(),
            'file_name' => fake()->fileName(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
