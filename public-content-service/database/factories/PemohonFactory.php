<?php

namespace Database\Factories;

use App\Models\Pemohon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PemohonFactory extends Factory
{
    protected $model = Pemohon::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'phone' => fake()->phoneNumber(),
            'token' => null,
            'otp' => null,
            'token_version' => 1,
            'remember_token' => Str::random(10),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
