<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRepositoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('content_detail_repositories')->where(function ($query) {
                $query->where('content_id', $this->content_id);
            })->ignore($id)],
            'file_path' => 'required',
            'thumbnail_path' => 'required',
            'publish_date' => 'required',
            'is_publicated' => 'required|boolean',
            'tag_id' => 'nullable|array',
        ];
    }
}
