<?php

namespace App\Services\DTOs;

class ResetPasswordDTO extends BaseDTO
{
    public ?string $token = null;

    public string $password;

    public string $password_confirmation;
}
