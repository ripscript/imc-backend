<?php

namespace App\Traits;

trait HasContentMapping
{
    public function applyContent($content)
    {
        if (! $content) {
            return;
        }

        $this->type = $content->type;
        $this->layout_id = $content->layout_id;
        $this->is_accessible = $content->is_accessible;
        $this->is_show_home = $content->is_show_home;
        $this->is_show_tag = $content->is_show_tag;

        if ($this->type === 'gallery') {
            $this->sub_type = $content->sub_type;
        }

        $this->content_detail = $content->getDetailBasedOnType();
    }
}
