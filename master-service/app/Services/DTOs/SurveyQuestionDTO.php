<?php

namespace App\Services\DTOs;

class SurveyQuestionDTO extends BaseDTO
{
    public string $question;

    public string $type;

    public bool $is_active;

    public ?int $order;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->question = $data['question'] ?? '';
        $this->type = $data['type'] ?? 'essay';
        $this->is_active = $data['is_active'] ?? false;
        $this->order = $data['order'] ?? 0;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}
