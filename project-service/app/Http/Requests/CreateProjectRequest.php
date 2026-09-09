<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_id' => ['nullable', 'integer', 'exists:pm_clients,id'],
            'partner_id' => ['nullable', 'integer', 'exists:pm_partners,id'],
            'category_id' => ['nullable', 'integer', 'exists:pm_project_categories,id'],
            'workflow_id' => ['nullable', 'integer', 'exists:pm_workflows,id'],
            'pic_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'target_end_date' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['nullable', 'string', 'url'],
        ];
    }
}
