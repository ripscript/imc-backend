<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\UpdatePemohonByAdminRequest;
use App\Http\Requests\UpdatePemohonByPemohonRequest;
use App\Http\Resources\PemohonResource;
use App\Models\Pemohon;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\UpdatePemohonByAdminDTO;
use App\Services\DTOs\UpdatePemohonByPemohonDTO;
use App\Services\PemohonService;
use Illuminate\Http\Request;

class PemohonController extends Controller
{
    public function __construct(
        protected PemohonService $pemohonService
    ) {}

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Pemohon::class);

            $data = $this->pemohonService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data pemohon');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $user = $this->pemohonService->detail($id);
            $this->authorize('view', $user);

            return ApiResponse::success(new PemohonResource($user), 'Berhasil mengambil data detail pemohon');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function updateByPemohon(UpdatePemohonByPemohonRequest $request)
    {
        try {
            $dto = new UpdatePemohonByPemohonDTO($request->validated());
            $result = $this->pemohonService->updateByPemohon($dto);

            return ApiResponse::success(new PemohonResource($result), 'Profil berhasil diperbarui');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateByAdmin(UpdatePemohonByAdminRequest $request, $id)
    {
        try {
            $dto = new UpdatePemohonByAdminDTO($request->validated());
            $result = $this->pemohonService->updateByAdmin((int) $id, $dto);
            $this->authorize('update', $result);

            return ApiResponse::success(new PemohonResource($result), 'Berhasil memperbarui status pemohon');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function resendVerification($id)
    {
        try {
            $pemohon = Pemohon::findOrFail($id);
            $this->authorize('update', $pemohon);

            $this->pemohonService->resendVerification((int) $id);

            return ApiResponse::success('', 'Berhasil mengirimkan email verifikasi ke pemohon');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete($id)
    {
        try {
            $pemohon = Pemohon::findOrFail($id);
            $this->authorize('delete', $pemohon);

            $this->pemohonService->delete((int) $id);

            return ApiResponse::success('', 'Berhasil menghapus pemohon');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
