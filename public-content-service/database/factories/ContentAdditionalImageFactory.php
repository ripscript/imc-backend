<?php

namespace Database\Factories;

use App\Models\ContentAdditionalImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentAdditionalImageFactory extends Factory
{
    protected $model = ContentAdditionalImage::class;

    public function definition(): array
    {
        return [
            'content_type' => 'App\Models\Content',
            'content_id' => 1,
            'image_path' => fake()->filePath(),
            'caption' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
