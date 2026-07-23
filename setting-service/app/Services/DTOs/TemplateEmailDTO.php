<?php

namespace App\Services\DTOs;

class TemplateEmailDTO extends BaseDTO
{
    public ?string $content;

    public ?string $subject;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->content = $data['content'] ?? null;
        $this->subject = $data['subject'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}
