<?php

namespace App\Services\DTOs;

class UserDTO extends BaseDTO
{
    public ?int $id = null;

    public string $email;

    public string $username;

    public string $name;

    public ?string $password = null;

    public int $role_id;

    public string $gender;

    public ?string $phone_number = null;

    public int $status;

    public ?int $satuan_kerja_id = null;

    public ?int $sub_satuan_kerja_id = null;

    public ?int $perwakilan_id = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}
