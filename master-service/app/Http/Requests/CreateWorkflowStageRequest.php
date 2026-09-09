<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWorkflowStageRequest extends FormRequest
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
            'sequence' => 'required|integer|min:1',
            'is_required' => 'nullable|boolean',
            'allow_skip' => 'nullable|boolean',
            'allow_repeat' => 'nullable|boolean',
        ];
    }
}
