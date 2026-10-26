<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'allowed_extension' => 'nullable|string|max:255',
            'max_size_mb' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }
}
