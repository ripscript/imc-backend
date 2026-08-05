<?php

namespace Database\Factories;

use App\Models\ContentTag;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentTagFactory extends Factory
{
    protected $model = ContentTag::class;

    public function definition(): array
    {
        return [
            'content_type' => 'App\Models\Content',
            'content_id' => 1,
            'tag_id' => 1,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
