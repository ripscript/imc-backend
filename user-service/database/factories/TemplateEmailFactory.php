<?php

namespace Database\Factories;

use App\Models\TemplateEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

class TemplateEmailFactory extends Factory
{
    protected $model = TemplateEmail::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->regexify('[A-Z0-9_]{5,20}'), 'subject' => fake()->sentence(4), 'body' => fake()->randomHtml(), 'is_active' => true, 'updated_by' => 'factory',
        ];
    }
}
