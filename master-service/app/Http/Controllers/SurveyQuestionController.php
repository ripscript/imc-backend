<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateSurveyQuestionRequest;
use App\Http\Requests\UpdateSurveyQuestionRequest;
use App\Http\Resources\SurveyQuestionResource;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\SurveyQuestionDTO;
use App\Services\SurveyQuestionService;
use Illuminate\Http\Request;

class SurveyQuestionController extends Controller
{
    public function __construct(
        protected SurveyQuestionService $surveyQuestionService
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->surveyQuestionService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data pertanyaan survei');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $question = $this->surveyQuestionService->detail($id);

            return ApiResponse::success(new SurveyQuestionResource($question), 'Berhasil mengambil detail pertanyaan survei');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateSurveyQuestionRequest $request)
    {
        try {
            $dto = new SurveyQuestionDTO($request->validated());
            $dto->created_by = auth('admin')->user()->name;
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->surveyQuestionService->create($dto);

            return ApiResponse::success(new SurveyQuestionResource($result), 'Berhasil menambahkan data pertanyaan survei', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateSurveyQuestionRequest $request, $id)
    {
        try {
            $dto = new SurveyQuestionDTO($request->validated());
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->surveyQuestionService->update($id, $dto);

            return ApiResponse::success(new SurveyQuestionResource($result), 'Berhasil memperbarui data pertanyaan survei');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            $this->surveyQuestionService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data pertanyaan survei');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function listActive()
    {
        try {
            $data = $this->surveyQuestionService->listActive();

            return ApiResponse::success($data, 'Berhasil mengambil data pertanyaan survei');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
