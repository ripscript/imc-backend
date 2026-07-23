<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePublicationRequest extends FormRequest
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
            'slug' => ['required', 'string', 'max:255', Rule::unique('content_detail_publications')->where(function ($query) {
                $query->where('content_id', $this->content_id);
            })],
            'headline' => 'required',
            'content' => 'required',
            'publish_date' => 'required',
            'is_publicated' => 'required|boolean',
            'kategori_content_id' => 'nullable|exists:ms_kategori_contents,id',
            'tag_id' => 'nullable|array',
        ];
    }
}
