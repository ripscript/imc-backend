<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProjectCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => ['nullable', 'string', 'max:255', Rule::unique('pm_project_categories', 'code')],
            'status' => 'nullable|string|in:active,inactive',
        ];
    }
}
