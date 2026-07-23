<?php

namespace App\Services\DTOs;

class UpdatePemohonByPemohonDTO extends BaseDTO
{
    public string $username;

    public string $name;

    public string $identity_number;

    public string $gender;

    public string $phone_number;

    public string $address;

    public string $organization;

    public string $job_title;

    public int $classification_pemohon_id;

    public string $identity_card_file_path;

    public ?string $legal_document_file_path = null;
}
