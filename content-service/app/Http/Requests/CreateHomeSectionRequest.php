<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'section_description' => 'nullable|string|max:255',
            'is_section' => 'required',
            'order' => 'required',
            'type' => 'required',
            'content' => [$this->isHeroCarousel() ? 'required' : 'nullable', 'array'],
            'content.*.id' => ['nullable', 'integer'],
            'content.*.title' => ['nullable', 'string', 'max:255'],
            'content.*.color_code' => ['nullable', 'string', 'max:50'],
            'content.*.link' => ['nullable', 'string'],
            'content.*.image_path' => ['nullable', 'string'],
            'content.*.description' => ['nullable', 'string'],
            'content.*.order' => ['nullable', 'integer'],
            'content.*.content_id' => ['nullable', 'integer'],
            'content.*.source_type' => [
                Rule::requiredIf(fn () => $this->isHeroCarousel()),
                'nullable',
                'in:publication,repository,gallery',
            ],
            'content.*.source_id' => [
                Rule::requiredIf(fn () => $this->isHeroCarousel()),
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $sourceType = $this->input("content.$index.source_type");
                    if ($sourceType && empty($value)) {
                        $fail('The source_id field is required when source_type is present.');

                        return;
                    }
                    if ($sourceType && $value) {
                        $table = match ($sourceType) {
                            'publication' => 'content_detail_publications',
                            'repository' => 'content_detail_repositories',
                            'gallery' => 'content_detail_galleries',
                            default => null,
                        };
                        if ($table && ! DB::table($table)->where('id', $value)->exists()) {
                            $fail('The selected source item does not exist.');
                        }
                    }
                },
            ],
            'content.*.button_text' => ['nullable', 'string', 'max:255'],
            'content.*.type' => ['nullable', 'string'],
            'content.*.url' => ['nullable', 'string'],
            'content.*.video_path' => ['nullable', 'string'],
            'content.*.consume_id' => ['nullable', 'integer'],
            'content.*.consume_type' => ['nullable', 'string'],
            'content.*.list' => ['nullable', 'array'],
            'content.*.list.*.id' => ['nullable', 'integer'],
            'content.*.list.*.title' => ['nullable', 'string', 'max:255'],
            'content.*.list.*.description' => ['nullable', 'string'],
            'content.*.list.*.file_path' => ['nullable', 'string'],
            'content.*.list.*.link' => ['nullable', 'string'],
            'content.*.list.*.order' => ['nullable', 'integer'],
            'content.*.groups' => ['nullable', 'array'],
            'content.id' => ['nullable', 'integer'],
            'content.content' => ['nullable', 'string'],
            'deleted_content_detail_id' => 'nullable|array',
            'deleted_content_detail_list_id' => 'nullable|array',
        ];
    }

    private function isHeroCarousel(): bool
    {
        return $this->input('type') === 'hero_carousel';
    }
}
