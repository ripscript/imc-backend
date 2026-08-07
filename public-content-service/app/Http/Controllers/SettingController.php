<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\SettingService;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function index()
    {
        $data = $this->settingService->index();

        return ApiResponse::success($data, 'Berhasil mengambil data pengaturan website');
    }
}
