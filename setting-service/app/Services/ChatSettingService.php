<?php

namespace App\Services;

use App\Helpers\LogActivity;
use App\Models\ChatSetting;
use App\Services\DTOs\ChatSettingDTO;

class ChatSettingService
{
    public function index()
    {
        return ChatSetting::first();
    }

    public function update(ChatSettingDTO $dto)
    {
        $data = ChatSetting::first();

        if (! $data) {
            throw new \Exception('Template pengaturan chat tidak ditemukan', 404);
        }

        $data->opening = $dto->opening;
        $data->closing = $dto->closing;
        $data->confirmation = $dto->confirmation;
        $data->another_question = $dto->another_question;
        $data->to_whatsapp = $dto->to_whatsapp;
        $data->updated_by = $dto->updated_by;
        $data->save();

        LogActivity::createCustom('pengaturan chat', 'Memperbarui pengaturan chat', $data);

        return $data;
    }
}
