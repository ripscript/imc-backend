<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'sequence' => 'nullable|integer|min:1',
            'is_required' => 'nullable|boolean',
            'allow_skip' => 'nullable|boolean',
            'allow_repeat' => 'nullable|boolean',
        ];
    }
}
