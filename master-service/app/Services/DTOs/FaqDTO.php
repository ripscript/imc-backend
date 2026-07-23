<?php

namespace App\Services\DTOs;

class FaqDTO extends BaseDTO
{
    public ?int $id = null;

    public string $title;

    public string $description;

    public bool $is_active;

    public int $kategori_faq_id;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}
