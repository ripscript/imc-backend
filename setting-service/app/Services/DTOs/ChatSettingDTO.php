<?php

namespace App\Services\DTOs;

class ChatSettingDTO extends BaseDTO
{
    public ?string $opening;

    public ?string $closing;

    public ?string $confirmation;

    public ?string $another_question;

    public ?string $to_whatsapp;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->opening = $data['opening'] ?? null;
        $this->closing = $data['closing'] ?? null;
        $this->confirmation = $data['confirmation'] ?? null;
        $this->another_question = $data['another_question'] ?? null;
        $this->to_whatsapp = $data['to_whatsapp'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}
