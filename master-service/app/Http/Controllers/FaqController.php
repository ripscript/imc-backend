<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Http\Resources\FaqResource;
use App\Services\DTOs\FaqDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\FaqService;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __construct(
        protected FaqService $faqService
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->faqService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data FAQ');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $faq = $this->faqService->detail($id);

            return ApiResponse::success(new FaqResource($faq), 'Berhasil mengambil data detail FAQ');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateFaqRequest $request)
    {
        try {
            $dto = new FaqDTO($request->validated());
            $dto->created_by = auth('admin')->user()->name;
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->faqService->create($dto);

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }

            return ApiResponse::success(new FaqResource($result), 'Berhasil menambahkan data FAQ', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateFaqRequest $request, $id)
    {
        try {
            $dto = new FaqDTO($request->validated());
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->faqService->update($id, $dto);

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }

            return ApiResponse::success(new FaqResource($result), 'Berhasil memperbarui data FAQ');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            $this->faqService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data FAQ');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
