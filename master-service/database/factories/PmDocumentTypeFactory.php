<?php

namespace Database\Factories;

use App\Models\PmDocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PmDocumentTypeFactory extends Factory
{
    protected $model = PmDocumentType::class;

    public function definition(): array
    {
        return [
            'code' => 'DT-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'name' => fake()->unique()->words(2, true),
            'allowed_extension' => 'pdf,docx',
            'max_size_mb' => 10,
            'status' => 'active',
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
