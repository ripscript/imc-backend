<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateChangeRequestRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string'],
            'impact_scope' => ['nullable', 'string'],
            'client_id' => ['nullable', 'integer', 'exists:pm_clients,id'],
            'project_stage_id' => ['nullable', 'integer', 'exists:pm_project_stages,id'],
            'proposed_changes' => ['required', 'array', 'min:1'],
        ];
    }
}
