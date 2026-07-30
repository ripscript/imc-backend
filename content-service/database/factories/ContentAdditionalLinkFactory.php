<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentAdditionalLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentAdditionalLinkFactory extends Factory
{
    protected $model = ContentAdditionalLink::class;

    public function definition(): array
    {
        return [
            'content_type' => Content::class,
            'content_id' => Content::factory(),
            'title' => fake()->sentence(4),
            'url' => fake()->url(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
