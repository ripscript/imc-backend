<?php

namespace App\Services\DTOs;

class KategoriFaqDTO
{
    public function __construct(
        public string $name,
        public ?string $created_by = null,
        public ?string $updated_by = null,
    ) {}
}
