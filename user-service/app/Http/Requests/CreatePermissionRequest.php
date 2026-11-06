<?php

namespace App\Http\Requests;

use App\Models\MsPermission;
use Illuminate\Foundation\Http\FormRequest;

class CreatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_\-]+$/',
            ],
            'action' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_\-]+$/',
                function ($attribute, $value, $fail) {
                    $name = strtolower(trim((string) $this->input('name')));
                    $action = strtolower(trim((string) $value));
                    $code = "{$name}.{$action}";

                    if (MsPermission::where('code', $code)->exists()) {
                        $fail("Kode hak akses '{$code}' sudah ada di database.");
                    }
                },
            ],
            'label' => 'required|string|max:200',
            'group' => 'nullable|string|max:100',
        ];
    }
}
