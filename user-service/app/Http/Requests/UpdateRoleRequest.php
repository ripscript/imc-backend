<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'integer|exists:ms_permissions,id',
        ];
    }
}
