<?php

namespace App\Traits;

trait HasAdditionalContentMapping
{
    public function applyAdditionalContent($source): void
    {
        if (! $source) {
            return;
        }

        foreach ([
            'has_additional_image',
            'has_additional_video',
            'has_additional_link',
            'has_additional_file',
            'has_additional_text',
        ] as $field) {
            if (isset($source->$field)) {
                $this->$field = $source->$field;
            }
        }

        $this->content_additional_image = $source->contentAdditionalImage ?? [];
        $this->content_additional_video = $source->contentAdditionalVideo ?? [];
        $this->content_additional_link = $source->contentAdditionalLink ?? [];
        $this->content_additional_file = $source->contentAdditionalFile ?? [];
        $this->content_additional_text = $source->contentAdditionalText ?? null;
    }
}
