<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\ClassificationPemohonResource;
use App\Services\ClassificationPemohonService;
use Illuminate\Http\Request;

class ClassificationPemohonController extends Controller
{
    public function __construct(
        protected ClassificationPemohonService $classificationPemohonService
    ) {}

    public function index(Request $request)
    {
        $data = $this->classificationPemohonService->index();

        return ApiResponse::success(ClassificationPemohonResource::collection($data), 'Data classification pemohon berhasil diambil');
    }
}
