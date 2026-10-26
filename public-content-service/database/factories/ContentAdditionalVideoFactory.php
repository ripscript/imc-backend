<?php

namespace Database\Factories;

use App\Models\ContentAdditionalVideo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentAdditionalVideoFactory extends Factory
{
    protected $model = ContentAdditionalVideo::class;

    public function definition(): array
    {
        return [
            'content_type' => 'App\Models\Content',
            'content_id' => 1,
            'video_url' => fake()->url(),
            'thumbnail_path' => fake()->filePath(),
            'caption' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
