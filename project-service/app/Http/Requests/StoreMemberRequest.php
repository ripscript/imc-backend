<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['nullable', 'string', 'in:pic,member,approver'],
        ];
    }
}
