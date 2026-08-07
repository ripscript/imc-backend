<?php

namespace App\Services\DTOs;

class GlobalSearchDTO extends BaseDTO
{
    public string $q;
    public int $page = 1;
    public int $limit = 10;
}
