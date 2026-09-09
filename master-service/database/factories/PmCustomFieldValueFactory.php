<?php

namespace Database\Factories;

use App\Models\PmCustomField;
use App\Models\PmCustomFieldValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class PmCustomFieldValueFactory extends Factory
{
    protected $model = PmCustomFieldValue::class;

    public function definition(): array
    {
        return [
            'custom_field_id' => PmCustomField::factory(),
            'entity_type' => 'project',
            'entity_id' => fake()->unique()->numberBetween(1, 9999),
            'value' => fake()->text(50),
            'created_by' => 'factory',
            'updated_by' => 'factory',
        ];
    }
}
