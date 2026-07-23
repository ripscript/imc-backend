<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\UpdateChatSettingRequest;
use App\Http\Resources\ChatSettingResource;
use App\Services\ChatSettingService;
use App\Services\DTOs\ChatSettingDTO;

class ChatSettingController extends Controller
{
    public function __construct(protected ChatSettingService $chatSettingService) {}

    public function index()
    {
        try {
            return ApiResponse::success(new ChatSettingResource($this->chatSettingService->index()), 'Berhasil mengambil data pengaturan chat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateChatSettingRequest $req)
    {
        try {
            $dto = new ChatSettingDTO($req->validated());
            $dto->updated_by = auth()->user()->name;

            return ApiResponse::success(new ChatSettingResource($this->chatSettingService->update($dto)), 'Berhasil memperbarui data pengaturan chat');
        } catch (\Throwable $th) {
            if ($th->getCode() == 404) {
                return ApiResponse::error($th->getMessage(), '', 404);
            } throw $th;
        }
    }
}
