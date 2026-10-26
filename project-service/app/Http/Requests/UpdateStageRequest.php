<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'in:pending,in_progress,pending_clearance,completed,skipped'],
            'pic_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'allow_skip' => ['sometimes', 'boolean'],
            'allow_repeat' => ['sometimes', 'boolean'],
            'requires_approval' => ['sometimes', 'boolean'],
            'approver_user_ids' => ['nullable', 'array'],
            'approver_user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'depends_on_project_stage_id' => ['nullable', 'integer'],
            'tasks' => ['nullable', 'array'],
            'tasks.*.id' => ['nullable', 'integer'],
            'tasks.*.title' => ['sometimes', 'string', 'max:255'],
            'tasks.*.description' => ['nullable', 'string'],
            'tasks.*.status' => ['sometimes', 'string', 'in:pending,in_progress,done,cancelled'],
            'tasks.*.priority' => ['nullable', 'string', 'in:low,medium,high'],
            'tasks.*.assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'tasks.*.due_date' => ['nullable', 'date'],
            'deliverables' => ['nullable', 'array'],
            'deliverables.*.id' => ['nullable', 'integer'],
            'deliverables.*.name' => ['sometimes', 'string', 'max:255'],
            'deliverables.*.document_type_id' => ['nullable', 'integer', 'exists:pm_document_types,id'],
            'deliverables.*.is_required' => ['nullable', 'boolean'],
            'deliverables.*.status' => ['sometimes', 'string', 'in:pending,wip,completed'],
            'deliverables.*.due_date' => ['nullable', 'date'],
        ];
    }
}
