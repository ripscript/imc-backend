<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateDocumentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => ['nullable', 'string', 'max:255', Rule::unique('pm_document_types', 'code')],
            'allowed_extension' => 'required|string|max:255',
            'max_size_mb' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }
}
