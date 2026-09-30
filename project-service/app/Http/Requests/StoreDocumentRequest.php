<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'document_type_id' => ['nullable', 'integer', 'exists:pm_document_types,id'],
            'file_name' => ['required', 'string', 'max:255'],
            'file_path' => ['required', 'string', 'max:2048'],
            'file_size_kb' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
