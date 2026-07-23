<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
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
        try {
            $data = $this->tagService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data tag');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $tag = $this->tagService->detail($id);

            return ApiResponse::success(new TagResource($tag), 'Berhasil mengambil data detail tag');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateTagRequest $request)
    {
        try {
            $dto = new TagDTO($request->validated());
            $dto->created_by = auth('admin')->user()->name;
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->tagService->create($dto);

            return ApiResponse::success(new TagResource($result), 'Berhasil menambahkan data tag', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateTagRequest $request, $id)
    {
        try {
            $dto = new TagDTO($request->validated());
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->tagService->update($id, $dto);

            return ApiResponse::success(new TagResource($result), 'Berhasil memperbarui data tag');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            $this->tagService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data tag');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
