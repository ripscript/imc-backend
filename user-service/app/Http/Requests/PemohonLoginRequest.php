<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PemohonLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_or_username' => 'required',
            'password' => 'required|string|min:8|max:100',
        ];
    }
}
