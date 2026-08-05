<?php

namespace Database\Factories;

use App\Models\ContentDetailText;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentDetailTextFactory extends Factory
{
    protected $model = ContentDetailText::class;

    public function definition(): array
    {
        return [
            'content_id' => 1,
            'body' => fake()->paragraphs(3, true),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
