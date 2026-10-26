<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentDetailImageLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailImageLinkFactory extends Factory
{
    protected $model = ContentDetailImageLink::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'title' => fake()->sentence(4),
            'image_url' => fake()->imageUrl(),
            'url' => fake()->url(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
