<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\DTOs\DashboardDTO;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function jumlahPerangkat(Request $request)
    {
        $data = $this->dashboardService->jumlahPerangkat(new DashboardDTO($request->all()));

        return ApiResponse::success($data, 'Berhasil mengambil data jumlah perangkat');
    }

    public function grafikPengunjung(Request $request)
    {
        $data = $this->dashboardService->grafikPengunjung(new DashboardDTO($request->all()));

        return ApiResponse::success($data, 'Berhasil mengambil data pengunjung');
    }
}
