<?php

namespace Database\Factories;

use App\Models\ContentDetailGallery;
use App\Models\ContentDetailGalleryList;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailGalleryListFactory extends Factory
{
    protected $model = ContentDetailGalleryList::class;

    public function definition(): array
    {
        return [
            'content_detail_gallery_id' => ContentDetailGallery::factory(),
            'title' => fake()->sentence(4),
            'image_path' => 'uploads/'.fake()->uuid().'.jpg',
            'description' => fake()->paragraph(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
