<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content_id' => 'required|exists:contents,id',
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('content_detail_galleries')->where(function ($query) {
                $query->where('content_id', $this->content_id);
            })],
            'publish_date' => 'required',
            'is_publicated' => 'required|boolean',
            'tag_id' => 'nullable|array',
            'list' => 'required|array|min:1',
        ];
    }
}
