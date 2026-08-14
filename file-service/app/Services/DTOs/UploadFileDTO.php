<?php

namespace App\Services\DTOs;

class UploadFileDTO extends BaseDTO
{
    public $file;

    public ?string $path = null;
}
