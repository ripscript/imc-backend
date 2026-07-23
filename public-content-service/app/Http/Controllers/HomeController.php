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
        try {
            $data = $this->homeService->index();

            return ApiResponse::success($data, 'Berhasil mengambil list section beranda');
        } catch (\Throwable $th) {
            if ($th->getCode() == 404) {
                return ApiResponse::error($th->getMessage(), 404);
            }
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $content = $this->homeService->detail($id);

            return ApiResponse::success($content, 'Berhasil mengambil detail section');
        } catch (\Throwable $th) {
            if ($th->getCode() == 404) {
                return ApiResponse::error($th->getMessage(), '', 404);
            }
            throw $th;
        }
    }

    public function latestInformation()
    {
        try {
            $content = $this->homeService->latestInformation();

            return ApiResponse::success($content, 'Berhasil mengambil data informasi terkini');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function summaryPermohonan()
    {
        try {
            $data = $this->homeService->summaryPermohonan();

            return ApiResponse::success($data, 'Berhasil mengambil data summary permohonan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function sifatInformasi()
    {
        try {
            $data = $this->homeService->sifatInformasi();

            return ApiResponse::success($data, 'Berhasil mengambil data sifat informasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
