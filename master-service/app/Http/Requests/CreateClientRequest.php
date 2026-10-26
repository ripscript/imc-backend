<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'identifier' => ['nullable', 'string', 'max:255', Rule::unique('pm_clients', 'identifier')],
            'status' => 'nullable|string|in:active,inactive',
        ];
    }
}
