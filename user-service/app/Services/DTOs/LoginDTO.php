<?php

namespace App\Services\DTOs;

class LoginDTO extends BaseDTO
{
    public string $email_or_username;

    public string $password;
}
