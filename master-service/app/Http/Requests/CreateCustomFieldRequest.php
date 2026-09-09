<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCustomFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'entity_type' => 'required|string|max:50',
            'field_type' => ['required', 'string', Rule::in([
                'text', 'textarea', 'number', 'money', 'date', 'datetime',
                'boolean', 'select', 'multiselect', 'email', 'url', 'file',
            ])],
            'is_required' => 'nullable|boolean',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }
}
