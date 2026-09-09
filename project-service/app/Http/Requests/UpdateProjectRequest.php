<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'client_id' => ['sometimes', 'integer', 'exists:pm_clients,id'],
            'partner_id' => ['sometimes', 'integer', 'exists:pm_partners,id'],
            'category_id' => ['sometimes', 'integer', 'exists:pm_project_categories,id'],
            'workflow_id' => ['sometimes', 'integer', 'exists:pm_workflows,id'],
            'pic_user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'start_date' => ['sometimes', 'date'],
            'target_end_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'string', 'in:draft,pending_approval,active,on_hold,completed,cancelled'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['nullable', 'string', 'url'],
            'deleted_attachment_ids' => ['nullable', 'array'],
            'deleted_attachment_ids.*' => ['integer'],
        ];
    }
}
