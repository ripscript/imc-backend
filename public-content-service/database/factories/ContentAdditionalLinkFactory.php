<?php

namespace Database\Factories;

use App\Models\ContentAdditionalLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentAdditionalLinkFactory extends Factory
{
    protected $model = ContentAdditionalLink::class;

    public function definition(): array
    {
        return [
            'content_type' => 'App\Models\Content',
            'content_id' => 1,
            'title' => fake()->sentence(),
            'url' => fake()->url(),
            'icon_path' => fake()->filePath(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
