<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
            'stages' => 'nullable|array',
            'stages.*.name' => 'required|string|max:255',
            'stages.*.code' => 'nullable|string|max:255',
            'stages.*.sequence' => 'required|integer|min:1',
            'stages.*.is_required' => 'nullable|boolean',
            'stages.*.allow_skip' => 'nullable|boolean',
            'stages.*.allow_repeat' => 'nullable|boolean',
        ];
    }
}
