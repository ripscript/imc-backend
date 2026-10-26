<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\MsTag;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\TagDTO;
use App\Services\TagService;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function __construct(
        protected TagService $tagService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', MsTag::class);

        $data = $this->tagService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data tag');
    }

    public function detail($id)
    {
        $tag = $this->tagService->detail($id);
        $this->authorize('view', $tag);

        return ApiResponse::success(new TagResource($tag), 'Berhasil mengambil data detail tag');
    }

    public function create(CreateTagRequest $request)
    {
        $this->authorize('create', MsTag::class);

        $dto = new TagDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->tagService->create($dto);

        return ApiResponse::success(new TagResource($result), 'Berhasil menambahkan data tag', 201);
    }

    public function update(UpdateTagRequest $request, $id)
    {
        $dto = new TagDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->tagService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new TagResource($result), 'Berhasil memperbarui data tag');
    }

    public function destroy($id)
    {
        $this->authorize('delete', MsTag::class);

        $this->tagService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus data tag');
    }
}
