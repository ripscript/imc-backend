<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateQuickLinkRequest;
use App\Http\Requests\UpdateQuickLinkRequest;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\QuickLinkDTO;
use App\Services\QuickLinkService;
use Illuminate\Http\Request;

class QuickLinkController extends Controller
{
    public function __construct(protected QuickLinkService $quickLinkService) {}

    public function index(Request $request)
    {
        try {
            return ApiResponse::success($this->quickLinkService->index(QueryFilterDTO::fromRequest($request)), 'Berhasil mengambil data link cepat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            return ApiResponse::success($this->quickLinkService->detail($id), 'Berhasil mengambil data link cepat');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateQuickLinkRequest $req)
    {
        try {
            $dto = new QuickLinkDTO($req->validated());
            $dto->created_by = $dto->updated_by = auth()->user()->name;

            return ApiResponse::success($this->quickLinkService->create($dto), 'Berhasil menambahkan data link cepat', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateQuickLinkRequest $req, $id)
    {
        try {
            $dto = new QuickLinkDTO($req->validated());
            $dto->updated_by = auth()->user()->name;

            return ApiResponse::success($this->quickLinkService->update($id, $dto), 'Berhasil memperbarui data link cepat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete($id)
    {
        try {
            $this->quickLinkService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data link cepat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
