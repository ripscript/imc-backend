<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateHomeSectionRequest;
use App\Http\Requests\UpdateHomeSectionRequest;
use App\Services\DTOs\ContentDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(protected HomeService $homeService) {}

    public function index(Request $request)
    {
        try {
            return ApiResponse::success($this->homeService->index(QueryFilterDTO::fromRequest($request)), 'Berhasil mengambil data section');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            return ApiResponse::success($this->homeService->detail($id), 'Berhasil mengambil data detail section');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(CreateHomeSectionRequest $req)
    {
        try {
            $dto = new ContentDTO($req->validated());
            $dto->created_by = $dto->updated_by = auth()->user()->name;

            return ApiResponse::success($this->homeService->create($dto), 'Berhasil menambahkan data section', 201);
        } catch (\Exception $e) {
            if ($e->getCode() == 404) {
                return ApiResponse::error('Menu beranda tidak ditemukan', 404);
            } throw $e;
        }
    }

    public function update(UpdateHomeSectionRequest $req, $id)
    {
        try {
            $dto = new ContentDTO($req->validated());
            $dto->updated_by = auth()->user()->name;

            return ApiResponse::success($this->homeService->update($id, $dto), 'Berhasil memperbarui data section');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $this->homeService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus section');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
