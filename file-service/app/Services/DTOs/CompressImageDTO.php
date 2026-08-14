<?php

namespace App\Services\DTOs;

class CompressImageDTO extends BaseDTO
{
    public $file;

    public ?string $path = null;
}
