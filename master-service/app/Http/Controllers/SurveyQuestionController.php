<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateSurveyQuestionRequest;
use App\Http\Requests\UpdateSurveyQuestionRequest;
use App\Http\Resources\SurveyQuestionResource;
use App\Models\MsSurveyQuestion;
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
        $this->authorize('viewAny', MsSurveyQuestion::class);

        $data = $this->surveyQuestionService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data pertanyaan survei');
    }

    public function detail($id)
    {
        $question = $this->surveyQuestionService->detail($id);
        $this->authorize('view', $question);

        return ApiResponse::success(new SurveyQuestionResource($question), 'Berhasil mengambil detail pertanyaan survei');
    }

    public function create(CreateSurveyQuestionRequest $request)
    {
        $this->authorize('create', MsSurveyQuestion::class);

        $dto = new SurveyQuestionDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->surveyQuestionService->create($dto);

        return ApiResponse::success(new SurveyQuestionResource($result), 'Berhasil menambahkan data pertanyaan survei', 201);
    }

    public function update(UpdateSurveyQuestionRequest $request, $id)
    {
        $dto = new SurveyQuestionDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->surveyQuestionService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new SurveyQuestionResource($result), 'Berhasil memperbarui data pertanyaan survei');
    }

    public function destroy($id)
    {
        $this->authorize('delete', MsSurveyQuestion::class);

        $this->surveyQuestionService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus data pertanyaan survei');
    }

    public function listActive()
    {
        $data = $this->surveyQuestionService->listActive();

        return ApiResponse::success($data, 'Berhasil mengambil data pertanyaan survei');
    }
}
