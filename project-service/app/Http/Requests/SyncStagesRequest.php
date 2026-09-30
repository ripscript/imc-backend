<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncStagesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'workflow_id' => ['nullable', 'integer', 'exists:pm_workflows,id'],
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.id' => ['nullable', 'integer'],
            'stages.*.workflow_stage_id' => ['nullable', 'integer', 'exists:pm_workflow_stages,id'],
            'stages.*.name' => ['required', 'string', 'max:255'],
            'stages.*.sequence' => ['required', 'integer', 'min:1', 'distinct'],
            'stages.*.is_required' => ['nullable', 'boolean'],
            'stages.*.allow_skip' => ['nullable', 'boolean'],
            'stages.*.allow_repeat' => ['nullable', 'boolean'],
            'stages.*.description' => ['nullable', 'string'],
            'stages.*.requires_approval' => ['nullable', 'boolean'],
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

    public function messages()
    {
        return [
            'stages.*.sequence.distinct' => 'Nomor urut (sequence) stage tidak boleh duplikat.',
        ];
    }
}
