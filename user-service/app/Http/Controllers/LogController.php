<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\LogService;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function __construct(
        protected LogService $logService
    ) {}

    public function activity(Request $request)
    {
        try {
            $data = $this->logService->activity(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil daftar aktivitas');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function chat(Request $request)
    {
        try {
            $data = $this->logService->chat(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data log chatbot');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function chatDetail($session)
    {
        try {
            $data = $this->logService->chatDetail($session);

            return ApiResponse::success($data, 'Berhasil mengambil detail chat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
