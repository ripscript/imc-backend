<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomeSectionRequest extends FormRequest
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
            'content' => 'nullable|array',
            'deleted_content_detail_id' => 'nullable|array',
            'deleted_content_detail_list_id' => 'nullable|array',
        ];
    }
}
