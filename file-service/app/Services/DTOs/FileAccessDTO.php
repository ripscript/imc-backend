<?php

namespace App\Services\DTOs;

class FileAccessDTO extends BaseDTO
{
    public string $file;

    public bool $download = false;
}
