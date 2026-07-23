<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateHomeSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_title' => 'required|string|max:200',
            'is_section' => 'required',
            'order' => 'required',
            'type' => 'required',
        ];
    }
}
