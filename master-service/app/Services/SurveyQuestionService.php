<?php

namespace App\Services;

use App\Models\MsSurveyQuestion;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\SurveyQuestionDTO;
use Illuminate\Support\Facades\DB;

class SurveyQuestionService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('ms_survey_questions')
            ->select('id', 'question', 'type', 'is_active');

        if ($filter->is_active !== null) {
            $query->where('is_active', $filter->is_active);
        }

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['question', 'type']);
    }

    public function detail($id)
    {
        return MsSurveyQuestion::findOrFail($id);
    }

    public function create(SurveyQuestionDTO $dto)
    {
        $question = new MsSurveyQuestion;
        $question->question = $dto->question;
        $question->type = $dto->type;
        $question->is_active = $dto->is_active;
        $question->order = $dto->order ?? 0;
        $question->created_by = $dto->created_by;
        $question->updated_by = $dto->updated_by;
        $question->save();

        return $question;
    }

    public function update($id, SurveyQuestionDTO $dto)
    {
        $question = MsSurveyQuestion::findOrFail($id);
        $question->question = $dto->question;
        $question->is_active = $dto->is_active;
        $question->order = $dto->order ?? $question->order;
        $question->updated_by = $dto->updated_by;
        $question->save();

        return $question;
    }

    public function destroy($id)
    {
        MsSurveyQuestion::findOrFail($id)->delete();

        return true;
    }

    public function listActive()
    {
        $data = DB::table('ms_survey_questions')
            ->select('uuid', 'question', 'type')
            ->orderBy('order', 'asc')
            ->where('is_active', true)
            ->get();

        $skalaLikert = DB::table('ms_survey_skala_likert')
            ->select('id', 'label')
            ->orderBy('order')
            ->get();

        foreach ($data as $value) {
            if ($value->type == 'skala_likert') {
                $value->skala_likert = $skalaLikert;
            }
        }

        return $data;
    }
}
