<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\DateHelper;
use App\Models\Keberatan;
use App\Models\MsAlert;
use App\Models\MsPermohonanAksesData;
use App\Models\MsPermohonanFormatData;
use App\Models\MsPermohonanSalinanInformasi;
use App\Models\MsPerwakilan;
use App\Models\MsSatuanKerja;
use App\Models\Permohonan;
use App\Models\PermohonanAksesData;
use App\Models\PermohonanDestinationInstitution;
use App\Models\PermohonanFormatData;
use App\Models\PermohonanSalinanInformasi;
use App\Models\SiteSetting;
use App\Traits\AuthorizesPemohon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class PermohonanController extends Controller
{
    use AuthorizesPemohon;

    public static function maxDestinationInstitution()
    {
        return SiteSetting::first()->value('permohonan_max_destination_institution') ?? 3;
    }

    public function index(Request $request)
    {
        try {
            $pemohonId = auth('pemohon')->id();
            $query = DB::table('permohonan as pm')
                ->leftJoin('permohonan_destination_institutions as pdi', function($join) {
                    $join->on('pdi.permohonan_id', '=', 'pm.id');
                })
                ->leftJoin('ms_satuan_kerja as sk', function($join) {
                    $join->on('sk.id', '=', 'pdi.destination_institution_id')
                        ->where('pdi.destination_institution_type', 'App\\Models\\MsSatuanKerja');
                })
                ->leftJoin('ms_perwakilan as pw', function($join) {
                    $join->on('pw.id', '=', 'pdi.destination_institution_id')
                        ->where('pdi.destination_institution_type', 'App\\Models\\MsPerwakilan');
                })
                ->leftJoin('keberatan as k', function ($join) {
                    $join->on('pm.id', '=', 'k.permohonan_id')
                        ->whereNull('k.deleted_at');
                })
                ->select(
                    'pm.number',
                    'pm.detail',
                    'pm.status',
                    'pm.created_at',
                    'k.status as status_keberatan',
                    DB::raw("
                        json_agg(
                            json_build_object(
                                'name', COALESCE(sk.name, pw.full_name),
                                'status', pdi.status
                            )
                        ) as destination_institutions
                    "),
                    'pm.is_survey_filled'
                )
                ->where('pm.pemohon_id', $pemohonId)
                ->whereNull('pm.deleted_at');

            if ($request->filled('status')) {
                $statuses = is_array($request->status)
                    ? $request->status
                    : [$request->status];

                $query->whereIn('pm.status', $statuses);
            }
            
            if ($request->filled('destination_institution_name')) {
                $keyword = strtolower($request->destination_institution_name);

                $query->havingRaw("LOWER(string_agg(DISTINCT COALESCE(sk.name, pw.full_name), ',')) LIKE ?", ["%$keyword%"]);
            }

            $query->orderBy('pm.created_at', 'desc');
            $query->groupBy('pm.id', 'pm.number', 'pm.detail', 'pm.status', 'pm.created_at', 'k.status', 'pm.is_survey_filled');


            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['pm.number', 'pm.detail'], null, 'pm.updated_at');

            $data['data'] = collect($data['data'])->map(function ($item) {
                // $names = explode(',', $item->destination_institution_name ?? '');
                // $item->destination_institution_name = array_values(array_filter($names, fn($n) => !empty($n)));

                $institutions = json_decode($item->destination_institutions, true) ?? [];
                $item->destination_institutions = array_values(array_filter($institutions, function ($row) {
                    return !empty($row['name']);
                }));
                return $item;
            })->all();

            return ApiResponse::success($data, 'Berhasil mengambil data permohonan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'detail' => 'required|string',
                'purpose' => 'required|string',
                'additional_document_file_path' => 'nullable|string',
                'akses_data_id' => 'required|array',
                'format_data_id' => 'required|array',
                'salinan_informasi_id' => 'required|array'
            ]);

            $userName = auth('pemohon')->user()->name;

            if (auth('pemohon')->user()->status != 2) {
                DB::rollBack();
                return ApiResponse::error('Mohon lengkapi data diri Anda terlebih dahulu. Permohonan hanya dapat diajukan setelah akun Anda berhasil terverifikasi.', '', 422);
            }

            $data = new Permohonan();
            $data->pemohon_id = auth('pemohon')->id();
            $data->detail = $request->detail;
            $data->purpose = $request->purpose;
            $data->additional_document_file_path = $request->additional_document_file_path;
            $data->status = 0;
            $data->is_privacy_policy_accepted = $request->is_privacy_policy_accepted;
            $data->created_by = $userName;
            
            $data->save();
            
            // institusi tujuan
            if ($request->filled('institutions')) {
                if (count($request->institutions) > PermohonanController::maxDestinationInstitution()) {
                    DB::rollBack();
                    return ApiResponse::error('Jumlah institusi tujuan tidak boleh lebih dari '.PermohonanController::maxDestinationInstitution(), '', 422);
                }
                foreach ($request->institutions as $value) {
                    $destinationInstitutionType = $value['destination_institution_type'];
                    $destinationInstitutionId = $value['destination_institution_id'];
        
                    if ($destinationInstitutionType == MsPerwakilan::class) {
                        $institutionValid = MsPerwakilan::find($destinationInstitutionId);
                    } else if ($destinationInstitutionType == MsSatuanKerja::class) {
                        $institutionValid = MsSatuanKerja::find($destinationInstitutionId);
                    } else {
                        DB::rollBack();
                        return ApiResponse::error('Terdapat tipe institusi tujuan yang tidak valid', '', 422);
                    }
        
                    if (!$institutionValid) {
                        DB::rollBack();
                        return ApiResponse::error('Terdapat institusi tujuan yang tidak valid', '', 422);
                    }
                    
                    PermohonanDestinationInstitution::firstOrCreate(
                        [
                            'permohonan_id' => $data->id,
                            'destination_institution_type' => $destinationInstitutionType,
                            'destination_institution_id' => $destinationInstitutionId,
                        ], 
                        ['created_by' => $userName]
                    );
                }
            }

            // akses data
            $totalAksesDataValid = MsPermohonanAksesData::whereIn('id', $request->akses_data_id)->count();
            if ($totalAksesDataValid !== count($request->akses_data_id)) {
                DB::rollBack();
                return ApiResponse::error('Terdapat kategori akses data yang tidak valid', '', 422);
            }
            foreach ($request->akses_data_id as $value) {
                PermohonanAksesData::create([
                    'permohonan_id' => $data->id,
                    'akses_data_id' => $value
                ]);
            }

            // format data
            $totalFormatDataValid = MsPermohonanFormatData::whereIn('id', $request->format_data_id)->count();
            if ($totalFormatDataValid !== count($request->format_data_id)) {
                DB::rollBack();
                return ApiResponse::error('Terdapat kategori format data yang tidak valid', '', 422);
            }
            foreach ($request->format_data_id as $value) {
                PermohonanFormatData::create([
                    'permohonan_id' => $data->id,
                    'format_data_id' => $value
                ]);
            }

            // salinan informasi
            $totalSalinanInformasiValid = MsPermohonanSalinanInformasi::whereIn('id', $request->salinan_informasi_id)->count();
            if ($totalSalinanInformasiValid !== count($request->salinan_informasi_id)) {
                DB::rollBack();
                return ApiResponse::error('Terdapat kategori salinan informasi yang tidak valid', '', 422);
            }
            foreach ($request->salinan_informasi_id as $value) {
                PermohonanSalinanInformasi::create([
                    'permohonan_id' => $data->id,
                    'salinan_informasi_id' => $value
                ]);
            }

            activity('permohonan')
            ->performedOn($data)
            ->causedBy(auth('pemohon')->user())
            ->log('Permohonan diajukan oleh pemohon');

            DB::commit();
            return ApiResponse::success('', 'Berhasil membuat permohonan dengan nomor '.$data->number, 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function detail(Request $request) 
    {
        try {
            $request->validate([
                'number' => 'required|string'
            ]);

            $permohonan = Permohonan::findByNumber($request->number);
            $this->authorizePemohon($permohonan);

            $permohonan->akses_data_id = $permohonan->aksesData->pluck('akses_data_id');
            $permohonan->format_data_id = $permohonan->formatData->pluck('format_data_id');
            $permohonan->salinan_informasi_id = $permohonan->salinanInformasi->pluck('salinan_informasi_id');

            $permohonan->akses_data_name = $permohonan->aksesData->pluck('master.name');
            $permohonan->format_data_name = $permohonan->formatData->pluck('master.name');
            $permohonan->salinan_informasi_name = $permohonan->salinanInformasi->pluck('master.name');

            $permohonan->institutions = PermohonanDestinationInstitution::where('permohonan_id', $permohonan->id)->get()->map(function ($inst) {
                return [
                    'name' => $inst->name ?? null,
                    'destination_institution_id' => $inst->destination_institution_id,
                    'destination_institution_type' => $inst->destination_institution_type,
                ];
            });

            $permohonan->makeHidden(['id', 'pemohon_id', 'created_by', 'updated_by', 'deleted_at', 'aksesData', 'formatData', 'salinanInformasi']);
            $tanggapans = $permohonan->tanggapan;

            if ($tanggapans && $tanggapans->isNotEmpty()) {
                $tanggapans->each(function ($tanggapan) {
                    $institution = $tanggapan->permohonanDestinationInstitution;
                    $tanggapan->makeHidden([
                        'id', 'permohonan_id', 'verified_by', 'verified_at',
                        'rejected_by', 'rejected_at', 'answered_by', 'answered_at',
                        'permohonan_destination_institution_id', 'permohonanDestinationInstitution'
                    ]);

                    if ($institution === null) {
                        $tanggapan->status = $tanggapan->permohonan->status;
                        $tanggapan->due_date_response = null;
                        $tanggapan->remaining_days = null;
                        $tanggapan->alert = null;
                        $tanggapan->is_need_additional_time = false;
                        return;
                    }

                    $tanggapan->status = $institution->status;
                    $tanggapan->due_date_response = $institution->due_date_response;
                    $remainingDays = null;
                    if (!empty($institution->is_need_additional_time) && $institution->status == Permohonan::STATUS['DIPROSES']) {
                        $remainingDays = DateHelper::remainingDays($institution->due_date_extended, true);
                    } else if (!empty($institution->due_date_response) && $institution->status == Permohonan::STATUS['DIPROSES']) {
                        $remainingDays = DateHelper::remainingDays($institution->due_date_response, true);
                    }
                    $tanggapan->remaining_days = $remainingDays;
                    if ($tanggapan->status == Permohonan::STATUS['DIPROSES']) {
                        $tanggapan->alert = MsAlert::getMessageByCode('permohonan_diproses');
                        if ($tanggapan->permohonanDestinationInstitution?->is_need_additional_time) {
                            $tanggapan->is_need_additional_time = true;
                            $tanggapan->alert = $tanggapan->permohonanDestinationInstitution->reason_need_additional_time;
                        }
                    }
                });

                $permohonan->setRelation('tanggapan', $tanggapans);
            }

            return ApiResponse::success($permohonan, 'Berhasil mengambil detail permohonan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'number' => 'required|string',
                'detail' => 'required|string',
                'purpose' => 'required|string',
                'additional_document_file_path' => 'nullable|string',
                'akses_data_id' => 'required|array',
                'format_data_id' => 'required|array',
                'salinan_informasi_id' => 'required|array',
            ]);

            $data = Permohonan::findByNumber($request->number);
            $this->authorizePemohon($data);
            if ($data->status != Permohonan::STATUS['DIAJUKAN']) {
                DB::rollBack();
                return ApiResponse::error('Permohonan dalam proses atau yang sudah selesai tidak dapat diubah', '', 422);
            }

            $userName = auth('pemohon')->user()->name;

            $data->detail = $request->detail;
            $data->purpose = $request->purpose;
            $data->additional_document_file_path = $request->additional_document_file_path;
            $data->updated_by = $userName;
            $data->save();

            // institusi tujuan
            if ($request->has('institutions')) {
                if (count($request->institutions) == 0) {
                    PermohonanDestinationInstitution::where('permohonan_id', $data->id)->delete();
                } else {
                    if (count($request->institutions) > PermohonanController::maxDestinationInstitution()) {
                        DB::rollBack();
                        return ApiResponse::error('Jumlah institusi tujuan tidak boleh lebih dari '.PermohonanController::maxDestinationInstitution(), '', 422);
                    }

                    $arrDestinationInstitution = [];
                    foreach ($request->institutions as $value) {
                        $destinationInstitutionType = $value['destination_institution_type'];
                        $destinationInstitutionId = $value['destination_institution_id'];
            
                        if ($destinationInstitutionType == MsPerwakilan::class) {
                            $institutionValid = MsPerwakilan::find($destinationInstitutionId);
                        } else if ($destinationInstitutionType == MsSatuanKerja::class) {
                            $institutionValid = MsSatuanKerja::find($destinationInstitutionId);
                        } else {
                            DB::rollBack();
                            return ApiResponse::error('Terdapat tipe institusi tujuan yang tidak valid', '', 422);
                        }
            
                        if (!$institutionValid) {
                            DB::rollBack();
                            return ApiResponse::error('Terdapat institusi tujuan yang tidak valid', '', 422);
                        }
                        
                        $destinationExists = PermohonanDestinationInstitution::firstOrCreate(
                            [
                                'permohonan_id' => $data->id,
                                'destination_institution_type' => $destinationInstitutionType,
                                'destination_institution_id' => $destinationInstitutionId,
                            ], 
                            ['created_by' => $userName]
                        );
                        $arrDestinationInstitution[] = $destinationExists->id;
                    }
                    PermohonanDestinationInstitution::where('permohonan_id', $data->id)->whereNotIn('id', $arrDestinationInstitution)->delete();
                }
            }

            // akses data
            $totalAksesDataValid = MsPermohonanAksesData::whereIn('id', $request->akses_data_id)->count();
            if ($totalAksesDataValid !== count($request->akses_data_id)) {
                DB::rollBack();
                return ApiResponse::error('Terdapat kategori akses data yang tidak valid', '', 422);
            }
            foreach ($request->akses_data_id as $value) {
                PermohonanAksesData::firstOrCreate([
                    'permohonan_id' => $data->id,
                    'akses_data_id' => $value
                ]);
            }
            PermohonanAksesData::where('permohonan_id', $data->id)->whereNotIn('akses_data_id', $request->akses_data_id)->delete();

            // format data
            $totalFormatDataValid = MsPermohonanFormatData::whereIn('id', $request->format_data_id)->count();
            if ($totalFormatDataValid !== count($request->format_data_id)) {
                DB::rollBack();
                return ApiResponse::error('Terdapat kategori format data yang tidak valid', '', 422);
            }
            foreach ($request->format_data_id as $value) {
                PermohonanFormatData::firstOrCreate([
                    'permohonan_id' => $data->id,
                    'format_data_id' => $value
                ]);
            }
            PermohonanFormatData::where('permohonan_id', $data->id)->whereNotIn('format_data_id', $request->format_data_id)->delete();

            // salinan informasi
            $totalSalinanInformasiValid = MsPermohonanSalinanInformasi::whereIn('id', $request->salinan_informasi_id)->count();
            if ($totalSalinanInformasiValid !== count($request->salinan_informasi_id)) {
                DB::rollBack();
                return ApiResponse::error('Terdapat kategori salinan informasi yang tidak valid', '', 422);
            }
            foreach ($request->salinan_informasi_id as $value) {
                PermohonanSalinanInformasi::firstOrCreate([
                    'permohonan_id' => $data->id,
                    'salinan_informasi_id' => $value
                ]);
            }
            PermohonanSalinanInformasi::where('permohonan_id', $data->id)->whereNotIn('salinan_informasi_id', $request->salinan_informasi_id)->delete();

            activity('permohonan')
            ->performedOn($data)
            ->causedBy(auth('pemohon')->user())
            ->log('Permohonan diperbarui oleh pemohon');

            DB::commit();
            return ApiResponse::success('', 'Berhasil memperbarui permohonan dengan nomor '.$data->number);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'number' => 'required|string'
            ]);

            $permohonan = Permohonan::findByNumber($request->number);
            $this->authorizePemohon($permohonan);
            if ($permohonan->status != Permohonan::STATUS['DIAJUKAN']) {
                DB::rollBack();
                return ApiResponse::error('Permohonan dalam proses atau yang sudah selesai tidak dapat dibatalkan', '', 422);
            }
            $number = $permohonan->number;
            $permohonan->delete();
            
            activity('permohonan')
            ->performedOn($permohonan)
            ->causedBy(auth('pemohon')->user())
            ->log('Permohonan dibatalkan oleh pemohon');

            DB::commit();
            return ApiResponse::success('', 'Berhasil membatalkan permohonan dengan nomor '.$number);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }  
    
    public function verify(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'number' => 'required|string'
            ]);

            $permohonan = Permohonan::findByNumber($request->number);
            $this->authorizePemohon($permohonan);
            if (!in_array($permohonan->status, [Permohonan::STATUS['DIJAWAB'], Permohonan::STATUS['DITOLAK']])) {
                DB::rollBack();
                return ApiResponse::error('Anda belum bisa melakukan verifikasi pada permohonan ini', '', 403);
            }
            if ($permohonan->is_verified_by_pemohon) {
                DB::rollBack();
                return ApiResponse::error('Permohonan sudah diverifikasi sebelumnya', '', 422);
            }
            $number = $permohonan->number;
            $permohonan->is_verified_by_pemohon = true;
            $permohonan->save();
            
            activity('permohonan')
            ->performedOn($permohonan)
            ->causedBy(auth('pemohon')->user())
            ->log('Pemohon memverifikasi dan menerima jawaban permohonan. Proses dinyatakan selesai.');

            DB::commit();
            return ApiResponse::success('', 'Berhasil memverifikasi permohonan dengan nomor '.$number);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function track(Request $request) 
    {
        try {
            $request->validate([
                'number' => 'required|string'
            ]);

            $permohonan = Permohonan::findByNumber($request->number);
            if (!$permohonan) {
                return ApiResponse::error('Permohonan tidak ditemukan', null, 404);
            }

            $tracksQuery = Activity::where(function ($q) use ($permohonan) {
                $q->where('subject_type', Permohonan::class)
                ->where('subject_id', $permohonan->id);
            });

            if ($permohonan->keberatan) {
                $keberatan = $permohonan->keberatan;
                $tracksQuery->orWhere(function ($q) use ($keberatan) {
                    $q->where('subject_type', Keberatan::class)
                    ->where('subject_id', $keberatan->id);
                });
            }

            $tracks = $tracksQuery
                ->orderBy('created_at', 'asc')
                ->get([
                    'description',
                    'created_at'
                ]);

            return ApiResponse::success($tracks, 'Berhasil mengambil data track status permohonan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function setting() 
    {
        try {
            $setting = SiteSetting::select('permohonan_max_destination_institution')->first();

            return ApiResponse::success($setting, 'Berhasil mengambil pengaturan permohonan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
