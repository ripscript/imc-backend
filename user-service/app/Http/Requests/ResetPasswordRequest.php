<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'max:100',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->symbols(),
            ],
        ];
    }
}
