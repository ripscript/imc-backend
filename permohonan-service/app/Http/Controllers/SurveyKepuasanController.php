<?php

namespace App\Http\Controllers;

use App\Exports\SurveyKepuasanExport;
use App\Helpers\ApiResponse;
use App\Models\MsSurveyQuestion;
use App\Models\MsSurveySkalaLikert;
use App\Models\Permohonan;
use App\Models\SurveyKepuasan;
use App\Models\SurveyKepuasanAnswer;
use App\Traits\AuthorizesPemohon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SurveyKepuasanController extends Controller
{
    use AuthorizesPemohon;

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'permohonan_number' => 'required',
                'jawaban' => 'required|array',
                'jawaban.*.pertanyaan' => 'required',
                'jawaban.*.jawaban' => 'required'
            ]);

            $permohonan = Permohonan::findByNumber($request->permohonan_number);
            $this->authorizePemohon($permohonan);
            if (!in_array($permohonan->status, [Permohonan::STATUS['DIJAWAB'], Permohonan::STATUS['DITOLAK']])) {
                DB::rollBack();
                return ApiResponse::error('Anda belum bisa mengisi survei karena status permohonan belum selesai', '', 422);
            }
            if ($permohonan->is_survey_filled) {
                DB::rollBack();
                return ApiResponse::error('Anda sudah mengisi survei', '', 422);
            }
            $survey = SurveyKepuasan::create([
                'permohonan_id' => $permohonan->id,
                'pemohon_id' => auth('pemohon')->user()->id
            ]);

            $bobotSkalaLikert = MsSurveySkalaLikert::pluck('bobot', 'id')->toArray();
            $pertanyaanMap = MsSurveyQuestion::where('is_active', true)->get()->keyBy('uuid');
            $totalBobot = 0;
            $countLikert = 0;

            foreach ($request->jawaban as $value) {
                $pertanyaanUuid = $value['pertanyaan'];
                $jawaban = $value['jawaban'];

                $pertanyaan = $pertanyaanMap[$pertanyaanUuid] ?? null;
                if (!$pertanyaan) {
                    DB::rollBack();
                    return ApiResponse::error('Terdapat pertanyaan yang tidak valid', '', 422);
                }

                
                $bobot = null;
                if ($pertanyaan->type == 'skala_likert') {
                    if (!isset($bobotSkalaLikert[$jawaban])) {
                        DB::rollBack();
                        return ApiResponse::error('Jawaban skala likert tidak valid', '', 422);
                    }
                    $bobot = $bobotSkalaLikert[$jawaban];
                    $totalBobot += $bobot;
                    $countLikert++;
                }

                SurveyKepuasanAnswer::create([
                    'survey_kepuasan_id' => $survey->id,
                    'survey_question_id' => $pertanyaan->id,
                    'answer' => $jawaban,
                    'bobot' => $bobot
                ]);
            }

            $nilai = $countLikert ? $totalBobot / $countLikert : 0;
            $survey->update(['nilai' => $nilai]);
            $permohonan->update(['is_survey_filled' => true]);

            DB::commit();
            return ApiResponse::success('', 'Berhasil menambahkan data survei kepuasan', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function index(Request $request)
    {
        try {
            $query = DB::table('survey_kepuasan as sk')
                ->join('permohonan as pm', 'sk.permohonan_id', '=', 'pm.id')
                ->leftJoin('pemohon as p', 'sk.pemohon_id', '=', 'p.id')
                ->select(
                    'sk.id',
                    'pm.number as permohonan_number',
                    'sk.permohonan_id',
                    'sk.created_at',
                    'sk.nilai',
                    'p.name as nama_pemohon'
                )
                ->orderBy('sk.created_at', 'desc');
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['pm.number'], null, 'sk.updated_at');

            return ApiResponse::success($data, 'Berhasil mengambil data survey kepuasan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $survey = SurveyKepuasan::with('permohonan', 'pemohon')->findOrFail($id);
            $surveyAnswer = SurveyKepuasanAnswer::with('question')
                ->where('survey_kepuasan_id', $survey->id)
                ->get()
                ->sortBy('question.order')
                ->values();

            $jawaban = [];
            foreach ($surveyAnswer as $key => $value) {
                $jawaban[] = [
                    'pertanyaan' => $value->question?->question,
                    'order' => $value->question?->order,
                    'bobot' => $value->bobot,
                    'jawaban' => $value->readable_answer,
                ];
            }
            $survey->answers = $jawaban;

            return ApiResponse::success($survey, 'Berhasil mengambil detail survey kepuasan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function avgNilai()
    {
        try {
            $totalResponse = SurveyKepuasan::count();
            $avgTotalNilai = SurveyKepuasan::average('nilai');
            $pertanyaanSkalaLikert = MsSurveyQuestion::where([
                'is_active' => true,
                'type' => 'skala_likert'
            ])->orderBy('order')->get();
            $skorPerPertanyaan = SurveyKepuasanAnswer::select(
                'survey_question_id',
                DB::raw('AVG(bobot) as avg_bobot')
            )
            ->groupBy('survey_question_id')
            ->pluck('avg_bobot', 'survey_question_id')
            ->toArray();

            $pertanyaan = [];
            foreach ($pertanyaanSkalaLikert as $value) {
                $avg = $skorPerPertanyaan[$value->id] ?? 0;
                $pertanyaan[] = [
                    'pertanyaan' => $value->question,
                    'rata_rata_nilai' => round($avg, 2)
                ];
            }

            $data = [
                'total_response' => $totalResponse,
                'rata_rata_nilai' => round($avgTotalNilai, 2),
                'pertanyaan' => $pertanyaan
            ];

            return ApiResponse::success($data, 'Berhasil mengambil nilai survey kepuasan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new SurveyKepuasanExport($request), 'survey_kepuasan_'.date('Y-m-d').'.xlsx');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
