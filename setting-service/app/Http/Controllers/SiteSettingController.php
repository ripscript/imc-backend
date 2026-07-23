<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\UpdateSiteSettingRequest;
use App\Http\Resources\SiteSettingResource;
use App\Services\DTOs\SiteSettingDTO;
use App\Services\SiteSettingService;

class SiteSettingController extends Controller
{
    public function __construct(protected SiteSettingService $siteSettingService) {}

    public function index()
    {
        try {
            return ApiResponse::success(new SiteSettingResource($this->siteSettingService->index()), 'Berhasil mengambil data pengaturan website');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateSiteSettingRequest $req)
    {
        try {
            $dto = new SiteSettingDTO($req->validated());
            $dto->updated_by = auth()->user()->name;

            return ApiResponse::success(new SiteSettingResource($this->siteSettingService->update($dto)), 'Berhasil memperbarui data pengaturan website');
        } catch (\Throwable $th) {
            if ($th->getCode() == 404) {
                return ApiResponse::error($th->getMessage(), '', 404);
            } throw $th;
        }
    }
}
