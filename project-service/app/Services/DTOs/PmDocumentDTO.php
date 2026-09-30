<?php

namespace App\Services\DTOs;

class PmDocumentDTO extends BaseDTO
{
    public ?int $document_type_id = null;

    public ?string $file_name = null;

    public ?string $file_path = null;

    public ?int $file_size_kb = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}
