<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertCustomFieldValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity_type' => 'required|string|max:50',
            'entity_id' => 'required|integer',
            'values' => 'required|array',
        ];
    }
}
