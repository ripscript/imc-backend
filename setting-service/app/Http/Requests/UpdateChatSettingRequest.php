<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChatSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opening' => 'required|string',
            'closing' => 'required|string',
            'confirmation' => 'required|string',
            'another_question' => 'required|string',
            'to_whatsapp' => 'required|string',
        ];
    }
}
