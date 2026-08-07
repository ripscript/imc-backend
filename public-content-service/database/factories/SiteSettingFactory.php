<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteSettingFactory extends Factory
{
    protected $model = SiteSetting::class;

    public function definition(): array
    {
        return [
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'facebook_url' => fake()->url(),
            'instagram_url' => fake()->url(),
            'youtube_url' => fake()->url(),
            'twitter_url' => fake()->url(),
            'wa_number' => fake()->phoneNumber(),
        ];
    }
}
