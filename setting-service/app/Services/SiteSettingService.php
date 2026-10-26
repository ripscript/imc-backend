<?php

namespace App\Services;

use App\Helpers\LogActivity;
use App\Models\SiteSetting;
use App\Services\DTOs\SiteSettingDTO;

class SiteSettingService
{
    public function index()
    {
        return SiteSetting::first();
    }

    public function update(SiteSettingDTO $dto)
    {
        $data = SiteSetting::first();

        if (! $data) {
            throw new \Exception('Template pengaturan website tidak ditemukan', 404);
        }

        $data->address = $dto->address;
        $data->phone = $dto->phone;
        $data->wa_number = $dto->wa_number;
        $data->email = $dto->email;
        $data->facebook_url = $dto->facebook_url;
        $data->youtube_url = $dto->youtube_url;
        $data->instagram_url = $dto->instagram_url;
        $data->twitter_url = $dto->twitter_url;
        if ($dto->menu_max_show_front !== null) {
            $data->menu_max_show_front = $dto->menu_max_show_front;
        }
        if ($dto->max_file_size !== null) {
            $data->max_file_size = $dto->max_file_size;
        }
        $data->updated_by = $dto->updated_by;
        $data->save();

        LogActivity::createCustom('pengaturan website', 'Memperbarui pengaturan website', $data);

        return $data;
    }
}
