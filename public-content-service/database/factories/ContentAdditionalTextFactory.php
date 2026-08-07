<?php

namespace Database\Factories;

use App\Models\ContentAdditionalText;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentAdditionalTextFactory extends Factory
{
    protected $model = ContentAdditionalText::class;

    public function definition(): array
    {
        return [
            'content_type' => 'App\Models\Content',
            'content_id' => 1,
            'text' => fake()->paragraph(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
