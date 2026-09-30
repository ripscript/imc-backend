<?php

namespace App\Support;

use App\Models\PmCustomFieldValue;

/**
 * Reads custom-field values for an entity (e.g. project), keyed by field
 * code with master-service-compatible type casting.
 */
class CustomFieldValues
{
    public static function forEntity(string $entityType, int $entityId): array
    {
        $values = PmCustomFieldValue::with('field')
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get();

        $result = [];

        foreach ($values as $row) {
            if (! $row->field) {
                continue;
            }

            $result[$row->field->code] = self::castValue($row->field->field_type, $row->value);
        }

        return $result;
    }

    private static function castValue(?string $fieldType, ?string $value)
    {
        if ($value === null) {
            return null;
        }

        return match ($fieldType) {
            'number', 'money' => is_numeric($value) ? (int) $value : $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'select', 'multiselect' => self::isJson($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }

    private static function isJson(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }
        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
