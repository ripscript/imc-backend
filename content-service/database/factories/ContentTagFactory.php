<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentTag;
use App\Models\MsTag;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentTagFactory extends Factory
{
    protected $model = ContentTag::class;

    public function definition(): array
    {
        return [
            'content_type' => Content::class,
            'content_id' => Content::factory(),
            'tag_id' => MsTag::factory(),
            'created_by' => 'Admin',
            'updated_by' => 'Admin',
        ];
    }
}
