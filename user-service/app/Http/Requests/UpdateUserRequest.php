<?php

namespace App\Http\Requests;

use App\Rules\UniqueSoftDelete;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'email' => ['required', 'string', 'email', 'max:255', new UniqueSoftDelete('users', 'email', $id)],
            'username' => ['required', 'string', 'max:100', new UniqueSoftDelete('users', 'username', $id)],
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|max:100',
            'role_id' => 'required|integer',
            'gender' => 'required|in:1,2',
            'phone_number' => 'nullable|string|max:50',
            'status' => 'required|integer',
            'satuan_kerja_id' => 'nullable|integer',
            'sub_satuan_kerja_id' => 'nullable|integer',
            'perwakilan_id' => 'nullable|integer',
        ];
    }
}
