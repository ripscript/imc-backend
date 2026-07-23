<?php

namespace App\Services\DTOs;

class FileDTO extends BaseDTO
{
    public ?string $path;

    public ?string $file;

    public function __construct(array $data = [])
    {
        $this->path = $data['path'] ?? null;
        $this->file = $data['file'] ?? null;
    }
}
