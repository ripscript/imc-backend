<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content_id' => ['sometimes', 'integer', 'exists:contents,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail_path' => ['nullable', 'string'],
            'publish_date' => ['nullable', 'date'],
            'is_publicated' => ['sometimes', 'boolean'],
            'layout_id' => ['sometimes', 'integer', 'exists:ms_layout,id'],
            'kategori_content_id' => ['nullable', 'integer', 'exists:ms_kategori_contents,id'],
            'duration' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'integer', 'min:0'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'organization_address' => ['nullable', 'string'],
            'tag_id' => ['nullable', 'array'],
            'tag_id.*' => ['integer', 'exists:ms_tags,id'],
            'has_additional_image' => ['nullable', 'boolean'],
            'has_additional_video' => ['nullable', 'boolean'],
            'has_additional_file' => ['nullable', 'boolean'],
            'has_additional_link' => ['nullable', 'boolean'],
            'has_additional_text' => ['nullable', 'boolean'],
            'content_additional_image' => ['nullable', 'array'],
            'content_additional_image.*.id' => ['nullable', 'integer'],
            'content_additional_image.*.image_path' => ['required', 'string'],
            'content_additional_image.*.description' => ['nullable', 'string'],
            'content_additional_video' => ['nullable', 'array'],
            'content_additional_video.*.id' => ['nullable', 'integer'],
            'content_additional_video.*.title' => ['required', 'string'],
            'content_additional_video.*.type' => ['required', 'in:upload,link'],
            'content_additional_video.*.url' => ['nullable', 'string'],
            'content_additional_video.*.video_path' => ['nullable', 'string'],
            'content_additional_file' => ['nullable', 'array'],
            'content_additional_file.*.id' => ['nullable', 'integer'],
            'content_additional_file.*.file_path' => ['required', 'string'],
            'content_additional_file.*.title' => ['required', 'string'],
            'content_additional_file.*.description' => ['nullable', 'string'],
            'content_additional_link' => ['nullable', 'array'],
            'content_additional_link.*.id' => ['nullable', 'integer'],
            'content_additional_link.*.link' => ['required', 'string'],
            'content_additional_link.*.title' => ['required', 'string'],
            'content_additional_text' => ['nullable', 'array'],
            'content_additional_text.*.id' => ['nullable', 'integer'],
            'content_additional_text.*.title' => ['nullable', 'string'],
            'content_additional_text.*.description' => ['required', 'string'],
            'deleted_content_additional_image_id' => ['nullable', 'array'],
            'deleted_content_additional_video_id' => ['nullable', 'array'],
            'deleted_content_additional_file_id' => ['nullable', 'array'],
            'deleted_content_additional_link_id' => ['nullable', 'array'],
            'deleted_content_additional_text_id' => ['nullable', 'array'],
            'client_id' => ['sometimes', 'integer', 'exists:pm_clients,id'],
            'partner_id' => ['sometimes', 'integer', 'exists:pm_partners,id'],
            'category_id' => ['sometimes', 'integer', 'exists:pm_project_categories,id'],
            'workflow_id' => ['sometimes', 'integer', 'exists:pm_workflows,id'],
            'pic_user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'start_date' => ['sometimes', 'date'],
            'target_end_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'string', 'in:draft,pending_approval,active,on_hold,completed,cancelled'],
            'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['nullable', 'string', 'url'],
            'deleted_attachment_ids' => ['nullable', 'array'],
            'deleted_attachment_ids.*' => ['integer'],
        ];
    }
}
