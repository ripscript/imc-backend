<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'slug' => ['required', 'string', 'max:200', Rule::unique('menus', 'slug')->ignore($id)],
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:255',
            'type' => 'nullable|string',
            'is_active' => 'required',
            'is_show_front' => 'required|boolean',
            'order' => 'required',
            'layout_id' => 'required_if:has_sub,false',
            'is_accessible' => 'boolean',
            'is_show_home' => 'boolean',
            'is_show_tag' => 'boolean',
            'content' => 'nullable|array',
            'sub_type' => 'nullable|string',
            'is_show_side_menu' => 'nullable|boolean',
            'deleted_content_detail_id' => 'nullable|array',
            'deleted_content_detail_list_id' => 'nullable|array',
            'has_additional_image' => 'nullable|boolean',
            'has_additional_video' => 'nullable|boolean',
            'has_additional_file' => 'nullable|boolean',
            'has_additional_link' => 'nullable|boolean',
            'has_additional_text' => 'nullable|boolean',
            'content_additional_image' => 'nullable|array',
            'content_additional_video' => 'nullable|array',
            'content_additional_file' => 'nullable|array',
            'content_additional_link' => 'nullable|array',
            'content_additional_text' => 'nullable|array',
            'deleted_content_additional_image_id' => 'nullable|array',
            'deleted_content_additional_video_id' => 'nullable|array',
            'deleted_content_additional_file_id' => 'nullable|array',
            'deleted_content_additional_link_id' => 'nullable|array',
            'deleted_content_additional_text_id' => 'nullable|array',
        ];
    }
}
