<?php

namespace Database\Factories;

use App\Models\Pemohon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pemohon>
 */
class PemohonFactory extends Factory
{
    protected static ?string $password;

    protected $model = Pemohon::class;

    public function definition(): array
    {
        return [
            'registration_number' => fake()->unique()->numerify('REG-#####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'identity_number' => fake()->unique()->numerify('3201##############'),
            'gender' => fake()->randomElement([1, 2]),
            'organization' => fake()->company(),
            'job_title' => fake()->jobTitle(),
            'username' => fake()->unique()->userName(),
            'phone_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'status' => 1,
            'password' => static::$password ??= Hash::make('password'),
            'token_version' => 1,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
