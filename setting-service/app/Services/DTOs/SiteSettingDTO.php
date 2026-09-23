<?php

namespace App\Services\DTOs;

class SiteSettingDTO extends BaseDTO
{
    public ?string $address;

    public ?string $phone;

    public ?string $wa_number;

    public ?string $email;

    public ?string $facebook_url;

    public ?string $youtube_url;

    public ?string $instagram_url;

    public ?string $twitter_url;

    public ?int $menu_max_show_front;

    public ?int $max_file_size;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->address = $data['address'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->wa_number = $data['wa_number'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->facebook_url = $data['facebook_url'] ?? null;
        $this->youtube_url = $data['youtube_url'] ?? null;
        $this->instagram_url = $data['instagram_url'] ?? null;
        $this->twitter_url = $data['twitter_url'] ?? null;
        $this->menu_max_show_front = $data['menu_max_show_front'] ?? null;
        $this->max_file_size = $data['max_file_size'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}
