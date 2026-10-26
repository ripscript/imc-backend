<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentAdditionalImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentAdditionalImageFactory extends Factory
{
    protected $model = ContentAdditionalImage::class;

    public function definition(): array
    {
        return [
            'content_type' => Content::class,
            'content_id' => Content::factory(),
            'image_path' => 'uploads/'.fake()->uuid().'.jpg',
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
