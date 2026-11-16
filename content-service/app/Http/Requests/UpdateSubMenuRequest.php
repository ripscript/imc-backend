<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'menu_id' => 'required|exists:menus,id',
            'slug' => ['required', 'string', 'max:200', Rule::unique('sub_menus', 'slug')->where(function ($query) {
                $query->where('menu_id', $this->menu_id);
            })->ignore($id)],
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:255',
            'type' => 'nullable|string',
            'is_active' => 'required',
            'order' => 'required',
            'layout_id' => 'required_if:has_child,false',
            'is_accessible' => 'boolean',
            'is_show_image' => 'boolean',
            'is_show_home' => 'boolean',
            'is_show_tag' => 'boolean',
            'content' => 'nullable|array',
            'sub_type' => 'nullable|string',
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
