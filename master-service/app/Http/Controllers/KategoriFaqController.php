<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateKategoriFaqRequest;
use App\Http\Requests\UpdateKategoriFaqRequest;
use App\Http\Resources\KategoriFaqResource;
use App\Models\MsKategoriFaq;
use App\Services\DTOs\KategoriFaqDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\KategoriFaqService;
use Illuminate\Http\Request;

class KategoriFaqController extends Controller
{
    public function __construct(
        protected KategoriFaqService $kategoriFaqService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', MsKategoriFaq::class);

        $data = $this->kategoriFaqService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data kategori FAQ');
    }

    public function detail($id)
    {
        $kategoriFaq = $this->kategoriFaqService->detail($id);
        $this->authorize('view', $kategoriFaq);

        return ApiResponse::success(new KategoriFaqResource($kategoriFaq), 'Berhasil mengambil data detail kategori FAQ');
    }

    public function create(CreateKategoriFaqRequest $request)
    {
        $this->authorize('create', MsKategoriFaq::class);

        $dto = new KategoriFaqDTO($request->validated()['name']);
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->kategoriFaqService->create($dto);

        return ApiResponse::success(new KategoriFaqResource($result), 'Berhasil menambahkan data kategori FAQ', 201);
    }

    public function update(UpdateKategoriFaqRequest $request, $id)
    {
        $dto = new KategoriFaqDTO($request->validated()['name']);
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->kategoriFaqService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new KategoriFaqResource($result), 'Berhasil memperbarui data kategori FAQ');
    }

    public function destroy($id)
    {
        $this->authorize('delete', MsKategoriFaq::class);

        $this->kategoriFaqService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus data kategori FAQ');
    }
}
