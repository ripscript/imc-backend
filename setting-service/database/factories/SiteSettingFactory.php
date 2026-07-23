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
            'wa_number' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'facebook_url' => fake()->url(),
            'youtube_url' => fake()->url(),
            'instagram_url' => fake()->url(),
            'twitter_url' => fake()->url(),
            'updated_by' => 'factory',
        ];
    }
}
