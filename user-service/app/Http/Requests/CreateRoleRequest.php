<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:100|unique:ms_roles,code',
            'is_active' => 'nullable|boolean',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'integer|exists:ms_permissions,id',
        ];
    }
}
