<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:pending,in_progress,done,cancelled'],
            'priority' => ['nullable', 'string', 'in:low,medium,high'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
