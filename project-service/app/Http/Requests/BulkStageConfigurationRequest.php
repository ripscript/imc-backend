<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkStageConfigurationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.id' => ['required', 'integer', 'distinct'],
            'stages.*.name' => ['sometimes', 'string', 'max:255'],
            'stages.*.description' => ['nullable', 'string'],
            'stages.*.start_date' => ['nullable', 'date'],
            'stages.*.end_date' => ['nullable', 'date'],
            'stages.*.status' => ['sometimes', 'string', 'in:pending,in_progress,completed,skipped'],
            'stages.*.pic_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'stages.*.allow_skip' => ['sometimes', 'boolean'],
            'stages.*.allow_repeat' => ['sometimes', 'boolean'],
            'stages.*.requires_approval' => ['sometimes', 'boolean'],
            'stages.*.depends_on_project_stage_id' => ['nullable', 'integer'],
            'stages.*.tasks' => ['nullable', 'array'],
            'stages.*.tasks.*.id' => ['nullable', 'integer'],
            'stages.*.tasks.*.title' => ['sometimes', 'string', 'max:255'],
            'stages.*.tasks.*.description' => ['nullable', 'string'],
            'stages.*.tasks.*.status' => ['sometimes', 'string', 'in:pending,in_progress,done,cancelled'],
            'stages.*.tasks.*.priority' => ['nullable', 'string', 'in:low,medium,high'],
            'stages.*.tasks.*.assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'stages.*.tasks.*.due_date' => ['nullable', 'date'],
            'stages.*.deliverables' => ['nullable', 'array'],
            'stages.*.deliverables.*.id' => ['nullable', 'integer'],
            'stages.*.deliverables.*.name' => ['sometimes', 'string', 'max:255'],
            'stages.*.deliverables.*.document_type_id' => ['nullable', 'integer', 'exists:pm_document_types,id'],
            'stages.*.deliverables.*.is_required' => ['nullable', 'boolean'],
            'stages.*.deliverables.*.status' => ['sometimes', 'string', 'in:pending,wip,completed'],
            'stages.*.deliverables.*.due_date' => ['nullable', 'date'],
        ];
    }
}
