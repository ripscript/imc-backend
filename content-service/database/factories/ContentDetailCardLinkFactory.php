<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentDetailCardLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailCardLinkFactory extends Factory
{
    protected $model = ContentDetailCardLink::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'title' => fake()->sentence(4),
            'image_url' => fake()->imageUrl(),
            'description' => fake()->paragraph(),
            'url' => fake()->url(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
