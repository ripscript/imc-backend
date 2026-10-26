<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'stages.*.description' => 'nullable|string',
            'stages.*.requires_approval' => 'nullable|boolean',
            'stages.*.tasks' => 'nullable|array',
            'stages.*.tasks.*.title' => 'required|string|max:255',
            'stages.*.tasks.*.description' => 'nullable|string',
            'stages.*.tasks.*.priority' => 'nullable|string|in:low,medium,high',
            'stages.*.deliverables' => 'nullable|array',
            'stages.*.deliverables.*.name' => 'required|string|max:255',
            'stages.*.deliverables.*.document_type_id' => 'nullable|integer|exists:pm_document_types,id',
            'stages.*.deliverables.*.is_required' => 'nullable|boolean',
        ];
    }
}
