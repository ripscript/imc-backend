<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliverableRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'document_type_id' => ['nullable', 'integer', 'exists:pm_document_types,id'],
            'is_required' => ['nullable', 'boolean'],
            'status' => ['sometimes', 'string', 'in:pending,wip,completed'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
