<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'content_id' => $this->content_id,
            'type' => $this->type,
            'section_title' => $this->section_title,
            'section_description' => $this->section_description,
            'title' => $this->title,
            'slug' => $this->slug,
            'layout' => $this->layout,
            'order' => $this->order,
            'is_section' => $this->is_section,
            'is_accessible' => $this->is_accessible,
            'sub_type' => $this->sub_type,
            'content' => $this->resolveContent(),
            'meta' => $this->meta ?? null,
            'breadcrumb' => $this->breadcrumb,
            'parent_menu' => $this->when(isset($this->parent_menu), $this->parent_menu),
            'image_link' => $this->whenLoaded('contentDetailImageLink'),
            'image_carousel' => $this->whenLoaded('contentDetailImageCarousel'),
            'hero_carousel' => $this->whenLoaded('contentDetailHeroCarousel'),
            'card_link' => $this->whenLoaded('contentDetailCardLink'),
            'video_carousel' => $this->whenLoaded('contentDetailVideoCarousel'),
            'accordion_group' => $this->whenLoaded('contentDetailAccordionGroup'),
            'text' => $this->whenLoaded('contentDetailText'),
            'consume' => $this->whenLoaded('contentDetailConsume'),
            'agenda' => $this->whenLoaded('contentDetailAgenda'),
            'additional_image' => $this->whenLoaded('contentAdditionalImage'),
            'additional_video' => $this->whenLoaded('contentAdditionalVideo'),
            'additional_link' => $this->whenLoaded('contentAdditionalLink'),
            'additional_file' => $this->whenLoaded('contentAdditionalFile'),
            'additional_text' => $this->content_additional_text ?? null,
        ];
    }

    /**
     * Khusus organizational_structure, bungkus content jadi { data, setting }.
     * Tipe lain tetap return content apa adanya.
     */
    private function resolveContent()
    {
        if ($this->type === 'organizational_structure') {
            return [
                'data' => $this->content,
                'setting' => [
                    'is_show_image' => (bool) $this->is_show_image,
                ],
            ];
        }

        return $this->content;
    }
}
