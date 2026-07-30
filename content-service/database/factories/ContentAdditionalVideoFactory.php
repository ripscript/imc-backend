<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentAdditionalVideo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentAdditionalVideoFactory extends Factory
{
    protected $model = ContentAdditionalVideo::class;

    public function definition(): array
    {
        return [
            'content_type' => Content::class,
            'content_id' => Content::factory(),
            'video_url' => fake()->url(),
            'order' => fake()->numberBetween(1, 10),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
