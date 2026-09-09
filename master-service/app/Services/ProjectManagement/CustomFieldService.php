<?php

namespace App\Services\ProjectManagement;

use App\Exceptions\Domain\ValidationException;
use App\Models\PmCustomField;
use App\Models\PmCustomFieldValue;
use App\Services\DTOs\CustomFieldDTO;
use App\Services\DTOs\CustomFieldValueDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\QueryService;
use Illuminate\Support\Facades\DB;

class CustomFieldService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = PmCustomField::query();

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name', 'code']);
    }

    public function detail($id)
    {
        return PmCustomField::findOrFail($id);
    }

    public function create(CustomFieldDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            $field = new PmCustomField;
            $field->name = $dto->name;
            $field->entity_type = $dto->entity_type;
            $field->field_type = $dto->field_type;
            $field->is_required = $dto->is_required ?? false;
            $field->status = $dto->status ?? 'active';
            $field->created_by = $dto->created_by;
            $field->updated_by = $dto->updated_by;
            $field->save();

            $field->code = ProjectCodeGenerator::customField($field->entity_type, $field->id);
            $field->save();

            return $field;
        });
    }

    public function update($id, CustomFieldDTO $dto)
    {
        $field = PmCustomField::findOrFail($id);

        if ($dto->name !== null) {
            $field->name = $dto->name;
        }
        if ($dto->is_required !== null) {
            $field->is_required = $dto->is_required;
        }
        if ($dto->status !== null) {
            $field->status = $dto->status;
        }
        $field->updated_by = $dto->updated_by;
        $field->save();

        return $field;
    }

    public function destroy($id)
    {
        PmCustomField::findOrFail($id)->delete();

        return true;
    }

    public function getValues(string $entityType, $entityId)
    {
        $rows = PmCustomFieldValue::with('customField')
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get();

        $values = [];
        foreach ($rows as $row) {
            if ($row->customField) {
                $values[$row->customField->code] = $this->castValue($row->customField, $row->value);
            }
        }

        return [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'values' => $values,
        ];
    }

    public function upsertValues(CustomFieldValueDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            foreach ($dto->values as $code => $value) {
                $field = PmCustomField::where('code', $code)
                    ->where('entity_type', $dto->entity_type)
                    ->first();

                if (! $field) {
                    throw new ValidationException("Custom field '{$code}' tidak ditemukan untuk entity {$dto->entity_type}");
                }

                PmCustomFieldValue::updateOrCreate(
                    [
                        'custom_field_id' => $field->id,
                        'entity_type' => $dto->entity_type,
                        'entity_id' => $dto->entity_id,
                    ],
                    [
                        'value' => $this->serializeValue($value),
                        'created_by' => $dto->created_by,
                        'updated_by' => $dto->updated_by,
                    ]
                );
            }

            return true;
        });
    }

    protected function serializeValue($value): ?string
    {
        if (is_array($value)) {
            return json_encode($value);
        }

        return $value === null ? null : (string) $value;
    }

    protected function castValue(PmCustomField $field, ?string $value)
    {
        if ($value === null) {
            return null;
        }

        return match ($field->field_type) {
            'number', 'money' => is_numeric($value) ? (int) $value : $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'select', 'multiselect' => $this->isJson($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }

    protected function isJson($value): bool
    {
        if (! is_string($value)) {
            return false;
        }
        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
