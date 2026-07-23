<?php

namespace App\Services\DTOs;

class ForgotPasswordDTO extends BaseDTO
{
    public string $email;

    public string $ip;
}
