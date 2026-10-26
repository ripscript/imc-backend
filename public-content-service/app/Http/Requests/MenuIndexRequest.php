<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_show_front' => 'nullable|boolean',
        ];
    }
}
