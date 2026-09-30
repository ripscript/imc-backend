<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->exists('attachments')) {
            $this->merge([
                'attachments' => \App\Support\ProjectAttachments::normalize($this->input('attachments')),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach (\App\Support\ProjectAttachments::validate($this->input('attachments', [])) as $key => $message) {
                $validator->errors()->add($key, $message);
            }
        });
    }

    public function rules()
    {
        return [
            'content_id' => ['nullable', 'integer', 'exists:contents,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail_path' => ['nullable', 'string'],
            'publish_date' => ['nullable', 'date'],
            'is_publicated' => ['nullable', 'boolean'],
            'layout_id' => ['nullable', 'integer', 'exists:ms_layout,id'],
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
            'client_id' => ['nullable', 'integer', 'exists:pm_clients,id'],
            'partner_id' => ['nullable', 'integer', 'exists:pm_partners,id'],
            'category_id' => ['nullable', 'integer', 'exists:pm_project_categories,id'],
            'workflow_id' => ['nullable', 'integer', 'exists:pm_workflows,id'],
            'pic_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'target_end_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,pending_approval,active,on_hold,completed,cancelled'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*.file_url' => ['required', 'string', 'url', 'max:2048'],
            'attachments.*.file_name' => ['nullable', 'string', 'max:255'],
            'attachments.*.file_size_kb' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
