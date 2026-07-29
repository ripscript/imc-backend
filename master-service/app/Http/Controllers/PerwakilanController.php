<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreatePerwakilanRequest;
use App\Http\Requests\UpdatePerwakilanRequest;
use App\Http\Resources\PerwakilanResource;
use App\Models\MsPerwakilan;
use App\Services\DTOs\PerwakilanDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\PerwakilanService;
use Illuminate\Http\Request;

class PerwakilanController extends Controller
{
    public function __construct(
        protected PerwakilanService $perwakilanService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', MsPerwakilan::class);

        $result = $this->perwakilanService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($result, 'Berhasil mengambil data perwakilan');
    }

    public function detail($id)
    {
        $perwakilan = $this->perwakilanService->detail($id);
        $this->authorize('view', $perwakilan);

        return ApiResponse::success(new PerwakilanResource($perwakilan), 'Berhasil mengambil detail perwakilan');
    }

    public function create(CreatePerwakilanRequest $request)
    {
        $this->authorize('create', MsPerwakilan::class);

        $dto = new PerwakilanDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->perwakilanService->create($dto);

        return ApiResponse::success(new PerwakilanResource($result), 'Berhasil menambahkan data perwakilan', 201);
    }

    public function update(UpdatePerwakilanRequest $request, $id)
    {
        $dto = new PerwakilanDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->perwakilanService->update($id, $dto);

        $this->authorize('update', $result);

        return ApiResponse::success(new PerwakilanResource($result), 'Berhasil memperbarui data perwakilan');
    }

    public function destroy($id)
    {
        $this->authorize('delete', MsPerwakilan::class);

        $this->perwakilanService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus data perwakilan');
    }
}
