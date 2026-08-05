<?php

namespace Database\Factories;

use App\Models\ChatSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatSettingFactory extends Factory
{
    protected $model = ChatSetting::class;

    public function definition(): array
    {
        return [
            'opening' => fake()->sentence(),
            'closing' => fake()->sentence(),
            'to_whatsapp' => fake()->sentence(),
            'another_question' => fake()->sentence(),
            'confirmation' => fake()->sentence(),
        ];
    }
}
