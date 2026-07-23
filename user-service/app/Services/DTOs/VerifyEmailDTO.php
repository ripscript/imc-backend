<?php

namespace App\Services\DTOs;

class VerifyEmailDTO extends BaseDTO
{
    public string $email;

    public string $otp;
}
