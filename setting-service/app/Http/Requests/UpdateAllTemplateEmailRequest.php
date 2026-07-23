<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAllTemplateEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => 'required|array',
            'data.*.id' => 'required|integer',
            'data.*.content' => 'required|string',
            'data.*.subject' => 'nullable|string',
        ];
    }
}
