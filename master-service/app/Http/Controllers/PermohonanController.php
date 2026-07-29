<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\PermohonanResource;
use App\Services\PermohonanService;

class PermohonanController extends Controller
{
    public function __construct(
        protected PermohonanService $permohonanService
    ) {}

    public function aksesData()
    {
        $data = $this->permohonanService->aksesData();

        return ApiResponse::success(PermohonanResource::collection($data), 'Data akses data berhasil diambil');
    }

    public function formatData()
    {
        $data = $this->permohonanService->formatData();

        return ApiResponse::success(PermohonanResource::collection($data), 'Data format data berhasil diambil');
    }

    public function salinanInformasi()
    {
        $data = $this->permohonanService->salinanInformasi();

        return ApiResponse::success(PermohonanResource::collection($data), 'Data salinan informasi berhasil diambil');
    }
}
