<?php

namespace App\Http\Requests;

use App\Rules\UniqueSoftDelete;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', new UniqueSoftDelete('pemohon', 'email')],
            'username' => ['required', 'string', 'max:100', new UniqueSoftDelete('pemohon', 'username')],
            'name' => 'required|string|max:255',
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
            'identity_number' => ['max:16', new UniqueSoftDelete('pemohon', 'identity_number')],
            'classification_pemohon_id' => 'exists:ms_classification_pemohon,id',
        ];
    }
}
