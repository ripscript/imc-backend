<?php

namespace App\Services\DTOs;

class RegisterDTO extends BaseDTO
{
    public string $email;

    public string $name;

    public string $username;

    public string $password;

    public ?string $identity_number = null;

    public ?int $classification_pemohon_id = null;

    public ?bool $is_privacy_policy_accepted = null;
}
