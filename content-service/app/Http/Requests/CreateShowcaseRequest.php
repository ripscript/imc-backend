<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateShowcaseRequest extends FormRequest
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
            'slug' => ['required', 'string', 'max:255', Rule::unique('content_detail_showcases')->where(function ($query) {
                $query->where('content_id', $this->content_id);
            })],
            'description' => 'nullable|string',
            'thumbnail_path' => 'nullable|string',
            'publish_date' => 'nullable|date',
            'is_publicated' => 'required|boolean',
            'tag_id' => 'nullable|array',
            'has_additional_image' => 'nullable|boolean',
            'has_additional_video' => 'nullable|boolean',
            'has_additional_file' => 'nullable|boolean',
            'has_additional_link' => 'nullable|boolean',
            'has_additional_text' => 'nullable|boolean',
            'content_additional_image' => 'nullable|array',
            'content_additional_image.*.id' => 'nullable|integer',
            'content_additional_image.*.image_path' => 'required|string',
            'content_additional_image.*.description' => 'nullable|string',
            'content_additional_video' => 'nullable|array',
            'content_additional_video.*.id' => 'nullable|integer',
            'content_additional_video.*.title' => 'required|string',
            'content_additional_video.*.type' => 'required|in:upload,link',
            'content_additional_video.*.url' => 'nullable|string',
            'content_additional_video.*.video_path' => 'nullable|string',
            'content_additional_file' => 'nullable|array',
            'content_additional_file.*.id' => 'nullable|integer',
            'content_additional_file.*.file_path' => 'required|string',
            'content_additional_file.*.title' => 'required|string',
            'content_additional_file.*.description' => 'nullable|string',
            'content_additional_link' => 'nullable|array',
            'content_additional_link.*.id' => 'nullable|integer',
            'content_additional_link.*.link' => 'required|string',
            'content_additional_link.*.title' => 'required|string',
            'content_additional_text' => 'nullable|array',
            'content_additional_text.*.id' => 'nullable|integer',
            'content_additional_text.*.title' => 'nullable|string',
            'content_additional_text.*.description' => 'required|string',
            'deleted_content_additional_image_id' => 'nullable|array',
            'deleted_content_additional_video_id' => 'nullable|array',
            'deleted_content_additional_file_id' => 'nullable|array',
            'deleted_content_additional_link_id' => 'nullable|array',
            'deleted_content_additional_text_id' => 'nullable|array',
            'duration' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'budget' => 'nullable|integer|min:0',
            'organization_address' => 'nullable|string',
            'organization_name' => 'nullable|string|max:255',
        ];
    }
}
