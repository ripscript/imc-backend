<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateHomeSectionRequest;
use App\Http\Requests\UpdateHomeSectionRequest;
use App\Services\DTOs\ContentDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\HomeService;
use App\Models\Content;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(protected HomeService $homeService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Content::class);
        return ApiResponse::success($this->homeService->index(QueryFilterDTO::fromRequest($request)), 'Berhasil mengambil data section');
    }

    public function detail($id)
    {
        $result = $this->homeService->detail($id);
        $this->authorize('view', $result);
        return ApiResponse::success($result, 'Berhasil mengambil data detail section');
    }

    public function create(CreateHomeSectionRequest $req)
    {
        $dto = new ContentDTO($req->validated());
        $dto->created_by = $dto->updated_by = auth()->user()->name;
        $this->authorize('create', Content::class);

        return ApiResponse::success($this->homeService->create($dto), 'Berhasil menambahkan data section', 201);
    }

    public function update(UpdateHomeSectionRequest $req, $id)
    {
        $dto = new ContentDTO($req->validated());
        $dto->updated_by = auth()->user()->name;

        $result = $this->homeService->update($id, $dto);
        $this->authorize('update', $result);
        return ApiResponse::success($result, 'Berhasil memperbarui data section');
    }

    public function delete($id)
    {
        $model = Content::findOrFail($id);
        $this->authorize('delete', $model);
        $this->homeService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus section');
    }
}
