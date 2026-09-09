<?php

namespace App\Services;

class ProjectCodeGenerator
{
    public static function project(int $id): string
    {
        return 'IMC.'.date('Y').'-'.str_pad((string) $id, 3, '0', STR_PAD_LEFT);
    }
}
