<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\HomeService;

class HomeController extends Controller
{
    public function __construct(
        protected HomeService $homeService
    ) {}

    public function index()
    {
        $data = $this->homeService->index();

        return ApiResponse::success($data, 'Berhasil mengambil list section beranda');
    }

    public function detail($id)
    {
        $content = $this->homeService->detail($id);

        return ApiResponse::success($content, 'Berhasil mengambil detail section');
    }

    public function latestInformation()
    {
        $content = $this->homeService->latestInformation();

        return ApiResponse::success($content, 'Berhasil mengambil data informasi terkini');
    }

    public function summaryPermohonan()
    {
        $data = $this->homeService->summaryPermohonan();

        return ApiResponse::success($data, 'Berhasil mengambil data summary permohonan');
    }

    public function sifatInformasi()
    {
        $data = $this->homeService->sifatInformasi();

        return ApiResponse::success($data, 'Berhasil mengambil data sifat informasi');
    }
}
