<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\MsSurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyQuestionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_survey_questions')
                ->select('id','question', 'type', 'is_active');
            
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['question', 'type']);

            return ApiResponse::success($data, 'Berhasil mengambil data pertanyaan survei');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $question = MsSurveyQuestion::findOrFail($id);
            return ApiResponse::success($question, 'Berhasil mengambil detail pertanyaan survei');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'question' => 'required|string',
                'type' => 'required|in:skala_likert,essay',
                'is_active' => 'required|boolean',
            ]);

            $user = auth('admin')->user()->name;

            $question = new MsSurveyQuestion();
            $question->question = $request->question;
            $question->type = $request->type;
            $question->is_active = $request->is_active;
            $question->order = $request->order ?? 0;
            $question->created_by = $user;
            $question->updated_by = $user;
            $question->save();

            DB::commit();
            return ApiResponse::success($question, 'Berhasil menambahkan data pertanyaan survei', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $question = MsSurveyQuestion::findOrFail($id);
            
            $request->validate([
                'question' => 'required|string',
                'is_active' => 'required|boolean'
            ]);

            $user = auth('admin')->user()->name;

            $question->question = $request->question;
            $question->is_active = $request->is_active;
            $question->order = $request->order ?? $question->order;
            $question->updated_by = $user;
            $question->save();

            DB::commit();
            return ApiResponse::success($question, 'Berhasil memperbarui data pertanyaan survei');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            MsSurveyQuestion::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data pertanyaan survei');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function listActive()
    {
        try {
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
            
            return ApiResponse::success($data, 'Berhasil mengambil data pertanyaan survei');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

}
