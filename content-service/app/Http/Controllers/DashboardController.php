<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function jumlahPerangkat(Request $request)
    {
        try {
            $data = $this->dashboardService->jumlahPerangkat($request->all());

            return ApiResponse::success($data, 'Berhasil mengambil data jumlah perangkat');
        } catch (\Throwable $th) {
            if ($th->getCode() == 401) {
                return ApiResponse::error($th->getMessage(), '', 401);
            }
            throw $th;
        }
    }

    public function grafikPengunjung(Request $request)
    {
        try {
            $data = $this->dashboardService->grafikPengunjung($request->all());

            return ApiResponse::success($data, 'Berhasil mengambil data pengunjung');
        } catch (\Throwable $th) {
            if ($th->getCode() == 401) {
                return ApiResponse::error($th->getMessage(), '', 401);
            }
            throw $th;
        }
    }
}
