<?php

namespace Database\Factories;

use App\Models\ContentDetailGalleryList;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailGalleryListFactory extends Factory
{
    protected $model = ContentDetailGalleryList::class;

    public function definition(): array
    {
        return [
            'content_detail_gallery_id' => 1,
            'image_path' => fake()->filePath(),
            'caption' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
