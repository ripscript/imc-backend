<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
            'stages' => 'required|array|min:1',
            'stages.*.name' => 'required|string|max:255',
            'stages.*.code' => 'nullable|string|max:255',
            'stages.*.sequence' => 'required|integer|min:1',
            'stages.*.is_required' => 'nullable|boolean',
            'stages.*.allow_skip' => 'nullable|boolean',
            'stages.*.allow_repeat' => 'nullable|boolean',
        ];
    }
}
