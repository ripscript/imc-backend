<?php

namespace App\Http\Controllers;

use App\Exports\PermohonanExport;
use App\Helpers\ApiResponse;
use App\Helpers\DateHelper;
use App\Helpers\LogActivity;
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
use App\Models\PermohonanTanggapan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AdminPermohonanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('permohonan as pm')
                ->join('pemohon as p', function($join) {
                    $join->on('p.id', '=', 'pm.pemohon_id');
                })
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
                ->select(
                    'pm.id',
                    'pm.number',
                    'pm.detail',
                    'pm.status',
                    'pm.created_at',
                    'pm.due_date_response',
                    DB::raw('p.name AS pemohon_name'),
                    DB::raw('p.identity_number AS pemohon_identity_number'),
                    // DB::raw("string_agg(DISTINCT COALESCE(sk.name, pw.full_name), ',') as destination_institution_name")
                    DB::raw("
                        json_agg(
                            json_build_object(
                                'name', COALESCE(sk.name, pw.full_name),
                                'status', pdi.status,
                                'due_date_response', pdi.due_date_response,
                                'is_need_additional_time', pdi.is_need_additional_time,
                                'due_date_extended', pdi.due_date_extended
                            )
                        ) as destination_institutions
                    ")
                )
                ->whereNull('p.deleted_at')
                ->whereNull('pm.deleted_at');

            $user = auth('admin')->user();
            $role = $user->role->name;
            if ($role == 'Admin Satker/Perwakilan') {
                $query->whereExists(function($q) use ($user) {
                    $q->select(DB::raw(1))
                    ->from('permohonan_destination_institutions as pdi2')
                    ->whereRaw('pdi2.permohonan_id = pm.id')
                    ->where(function($q2) use ($user) {
                        if ($user->perwakilan_id) {
                            $q2->where('pdi2.destination_institution_type', 'App\\Models\\MsPerwakilan')
                                ->where('pdi2.destination_institution_id', $user->perwakilan_id);
                        } elseif ($user->satuan_kerja_id) {
                            $q2->where('pdi2.destination_institution_type', 'App\\Models\\MsSatuanKerja')
                                ->where('pdi2.destination_institution_id', $user->satuan_kerja_id);
                        }
                    });
                });
            }
            
            if ($request->filled('destination_institution_name')) {
                $keyword = strtolower($request->destination_institution_name);

                $query->havingRaw("LOWER(string_agg(DISTINCT COALESCE(sk.name, pw.full_name), ',')) LIKE ?", ["%$keyword%"]);
            }

            if ($request->filled('institution_type') && $request->filled('institution_id') && $request->filled('hari_kadaluwarsa')) {
                $query
                    ->where('pdi.destination_institution_type', 'App\\Models\\'.$request->institution_type)
                    ->where('pdi.destination_institution_id', $request->institution_id)
                    ->where('pdi.status', Permohonan::STATUS['DIPROSES'])
                    ->whereDate('pdi.due_date_response', '>', now())
                    ->whereDate('pdi.due_date_response', '<=', now()->addDays($request->hari_kadaluwarsa));
            }

            if ($request->filled('status')) {
                $statuses = is_array($request->status)
                    ? $request->status
                    : [$request->status];

                $query->whereIn('pm.status', $statuses);
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('pm.created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay(),
                ]);
            } elseif ($request->filled('start_date')) {
                $query->where('pm.created_at', '>=',
                    Carbon::parse($request->start_date)->startOfDay()
                );
            } elseif ($request->filled('end_date')) {
                $query->where('pm.created_at', '<=',
                    Carbon::parse($request->end_date)->endOfDay()
                );
            }
            
            $query->orderBy('pm.created_at', 'desc');
            $query->groupBy('pm.id', 'pm.number', 'pm.detail', 'pm.status', 'pm.created_at', 'pm.due_date_response', 'p.name', 'p.identity_number');

            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['pm.number', 'pm.detail', 'p.name', 'p.identity_number'], null, 'pm.updated_at');

            $data['data'] = collect($data['data'])->map(function ($item) {
                $item->remaining_days = ($item->status == Permohonan::STATUS['DIPROSES']) ?
                    DateHelper::remainingDays($item->due_date_response, true) :
                    null;
                    
                // $names = explode(',', $item->destination_institution_name ?? '');
                // $item->destination_institution_name = array_values(array_filter($names, fn($n) => !empty($n)));

                $institutions = json_decode($item->destination_institutions, true) ?? [];
                $item->destination_institutions = array_values(array_filter(array_map(function ($row) {
                    if (!empty($row['is_need_additional_time']) && $row['status'] == Permohonan::STATUS['DIPROSES']) {
                        $row['remaining_days'] = DateHelper::remainingDays($row['due_date_extended'], true);
                    } else if (!empty($row['due_date_response']) && $row['status'] == Permohonan::STATUS['DIPROSES']) {
                        $row['remaining_days'] = DateHelper::remainingDays($row['due_date_response'], true);
                    } else {
                        $row['remaining_days'] = null;
                    }
                    return $row;
                }, $institutions), function ($row) {
                    return !empty($row['name']);
                }));

                return $item;
            })->all();

            return ApiResponse::success($data, 'Berhasil mengambil data permohonan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id) 
    {
        try {
            $permohonan = Permohonan::with([
                'pemohon',
                'aksesData.master',
                'formatData.master',
                'salinanInformasi.master',
                'institutions'
            ])->findOrFail($id);

            $canAccess = $permohonan->checkAccessInstitution();
            if (!$canAccess) {
                DB::rollBack();
                return ApiResponse::error('Anda tidak memiliki akses ke permohonan ini', '', 403);
            }

            $permohonan->akses_data_name = $permohonan->aksesData->pluck('master.name');
            $permohonan->format_data_name = $permohonan->formatData->pluck('master.name');
            $permohonan->salinan_informasi_name = $permohonan->salinanInformasi->pluck('master.name');
            
            $permohonan->tanggapan->each(function ($tanggapan) {
                $institution = $tanggapan->permohonanDestinationInstitution;
                $canAnswer = $tanggapan->can_answer;

                if ($institution === null) {
                    $tanggapan->setAttribute('can_answer', false);
                    $tanggapan->setAttribute('can_add_time', false);
                    $tanggapan->setAttribute('status', $tanggapan->permohonan->status);
                    $tanggapan->setAttribute('remaining_days', null);
                    $tanggapan->setAttribute('template_reason_add_time', null);
                    return;
                }

                $todayIsDueDate = $canAnswer && 
                    Carbon::parse($institution->due_date_response)->lessThanOrEqualTo(Carbon::today()) &&
                    $institution->status == Permohonan::STATUS['DIPROSES'] && 
                    !$tanggapan->is_need_additional_time;
                $tanggapan->setAttribute('can_answer', $canAnswer);
                $tanggapan->setAttribute('can_add_time', $todayIsDueDate);
                if ($todayIsDueDate) {
                    $tanggapan->setAttribute('template_reason_add_time', MsAlert::getMessageByCode('permohonan_need_additional_time'));
                }
                $tanggapan->setAttribute('status', $institution->status);
                $remainingDays = null;
                if (!empty($institution->is_need_additional_time) && $institution->status == Permohonan::STATUS['DIPROSES']) {
                    $remainingDays = DateHelper::remainingDays($institution->due_date_extended, true);
                } else if (!empty($institution->due_date_response) && $institution->status == Permohonan::STATUS['DIPROSES']) {
                    $remainingDays = DateHelper::remainingDays($institution->due_date_response, true);
                }
                $tanggapan->setAttribute('remaining_days', $remainingDays);
            });

            
            $permohonan->makeHidden(['aksesData', 'formatData', 'salinanInformasi']);

            return ApiResponse::success($permohonan, 'Berhasil mengambil detail permohonan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'status' => 'required|in:1,2,3',
            ]);

            $permohonan = Permohonan::findOrFail($id);
            $user = auth('admin')->user()->name;
            $role = auth('admin')->user()->role->name;
            $statusBaru = (int) $request->status;
            $statusLama = (int) $permohonan->status;

            $canAccess = $permohonan->checkAccessInstitution();
            if (!$canAccess) {
                DB::rollBack();
                return ApiResponse::error('Anda tidak memiliki akses ke permohonan ini', '', 403);
            }

            if (in_array($statusLama, [
                Permohonan::STATUS['DIJAWAB'],
                Permohonan::STATUS['DITOLAK']
            ])) {
                DB::rollBack();
                return ApiResponse::error('Permohonan sudah selesai (dijawab/ditolak) dan tidak dapat diubah lagi.', 422);
            }

            $allowedNext = [
                Permohonan::STATUS['DIAJUKAN'] => [
                    Permohonan::STATUS['DITOLAK'],
                    Permohonan::STATUS['DIPROSES']
                ],
                Permohonan::STATUS['DIPROSES'] => [
                    Permohonan::STATUS['DIJAWAB'],
                ],
            ];

            if (!isset($allowedNext[$statusLama]) || !in_array($statusBaru, $allowedNext[$statusLama])) {
                DB::rollBack();
                return ApiResponse::error('Perubahan status tidak valid atau tidak berurutan.', 422);
            }

            if ($statusBaru === Permohonan::STATUS['DIJAWAB']) {
                $request->validate([
                    'tanggapan.permohonan_destination_institution_id' => 'required',
                    'tanggapan.fpt_file_path' => 'nullable|string',
                    'tanggapan.description' => 'required|string',
                    'tanggapan.attachment_file_path' => 'nullable|string',
                ]);
            }

            if ($statusBaru === Permohonan::STATUS['DITOLAK']) {
                $request->validate([
                    'tanggapan.fpt_file_path' => 'nullable|string',
                    'tanggapan.reason_rejected' => 'required|string',
                    'tanggapan.rejection_attachment_file_path' => 'nullable|string',
                ]);
            }

            $requestTanggapan = $request->tanggapan;
            $namaInstitusi = "-";
            
            switch ($statusBaru) {
                case Permohonan::STATUS['DIPROSES']:
                    if (!in_array($role, ['Superadmin', 'Admin Pusat'])) {
                        if ($permohonan->institutions->count() > 1) {
                            DB::rollBack();
                            return ApiResponse::error('Verifikasi permohonan dengan banyak institusi tujuan hanya dapat dilakukan oleh admin pusat', '', 403);
                        }
                    }
                    
                    foreach ($permohonan->institutions as $institution) {
                        $institution->due_date_response = DateHelper::addWorkingDays(Carbon::now(), Permohonan::JUMLAH_HARI_PROSES);
                        $institution->status = 1;
                        $institution->save();

                        $tanggapan = PermohonanTanggapan::firstOrCreate(
                            [
                                'permohonan_id' => $id, 
                                'permohonan_destination_institution_id' => $institution->id
                            ],
                            [
                                'verified_by' => $user,
                                'verified_at' => now()
                            ]
                        );
                    }
                    $permohonan->status = $statusBaru;
                    $permohonan->due_date_response = DateHelper::addWorkingDays(Carbon::now(), Permohonan::JUMLAH_HARI_PROSES);
                    $logMessage = [
                        'Permohonan diterima dan sedang diverifikasi oleh petugas',
                        'Permohonan dinyatakan lengkap dan layak diproses'
                    ];
                break;

                case Permohonan::STATUS['DIJAWAB']:
                    $tanggapan = PermohonanTanggapan::firstOrCreate([
                        'permohonan_id' => $id, 
                        'permohonan_destination_institution_id' => $requestTanggapan['permohonan_destination_institution_id']
                    ]);
                    if (!$tanggapan->can_answer) {
                        DB::rollBack();
                        return ApiResponse::error('Anda tidak memiliki akses untuk memberikan tanggapan ini', '', 403);
                    }
                    if ($tanggapan->permohonanDestinationInstitution->status == Permohonan::STATUS['DIJAWAB']) {
                        DB::rollBack();
                        return ApiResponse::error('Permohonan sudah selesai dijawab untuk institusi ini dan tidak dapat diubah lagi.', 422);
                    }
                    if (empty($tanggapan->number)) {
                        $tanggapan->number = PermohonanTanggapan::generateNumber();
                    }
                    $tanggapan->fill([
                        'fpt_file_path' => $requestTanggapan['fpt_file_path'] ?? "",
                        'description' => $requestTanggapan['description'],
                        'attachment_file_path' => $requestTanggapan['attachment_file_path'],
                        'answered_by' => $user,
                        'answered_at' => now(),
                    ]);
                    $tanggapan->save();
                    $tanggapan->permohonanDestinationInstitution->update([
                        'status' => $statusBaru,
                    ]);
                    $tanggapan->permohonan->syncStatusFromInstitutions();
                    $namaInstitusi = $tanggapan->permohonanDestinationInstitution->name ?? '-';
                    $logMessage = [
                        "Petugas dari {$namaInstitusi} menyiapkan data/informasi untuk permohonan.",
                        "Jawaban permohonan dari {$namaInstitusi} telah dikirim ke pemohon."
                    ];
                break;

                case Permohonan::STATUS['DITOLAK']:
                    $tanggapan = PermohonanTanggapan::firstOrCreate(['permohonan_id' => $id]);
                    if (empty($tanggapan->number)) {
                        $tanggapan->number = PermohonanTanggapan::generateNumber();
                    }
                    $tanggapan->fill([
                        'fpt_file_path' => $requestTanggapan['fpt_file_path'] ?? "",
                        'reason_rejected' => $requestTanggapan['reason_rejected'],
                        'rejection_attachment_file_path' => $requestTanggapan['rejection_attachment_file_path'],
                        'rejected_by' => $user,
                        'rejected_at' => now(),
                    ]);
                    $tanggapan->save();
                    $permohonan->institutions()->update([
                        'status' => $statusBaru,
                    ]);
                    $permohonan->status = $statusBaru;
                    $logMessage = [
                        'Permohonan diterima dan sedang diverifikasi oleh petugas',
                        'Permohonan ditolak saat verifikasi'
                    ];
                break;
            }

            $permohonan->updated_by = $user;
            $permohonan->save();

            foreach ($logMessage as $value) {
                activity('permohonan')
                    ->performedOn($permohonan)
                    ->causedBy(auth('admin')->user())
                    ->log($value);
            }

            DB::commit();

            if ($statusBaru == Permohonan::STATUS['DIPROSES']) {
                $this->emailService->sendTemplate('Pemberitahuan Diterima', $permohonan->pemohon->email, [
                    'tgl_permohonan' => DateHelper::formatTanggalIndo($permohonan->created_at), 
                    'nama' => $permohonan->pemohon->name, 
                    'alamat' => $permohonan->pemohon->address, 
                    'no_telepon' => $permohonan->pemohon->phone_number, 
                    'email' => $permohonan->pemohon->email, 
                    'no_permohonan' => $permohonan->number, 
                    'tgl_diproses' => DateHelper::formatTanggalIndo(now())
                ], 'PPID Kemlu – Permohonan Informasi Anda Telah Diterima');
            } else if ($statusBaru == Permohonan::STATUS['DIJAWAB']) {
                $salinanInformasi = $permohonan->salinanInformasi->pluck('master.name')->toArray();
                $withAttachment = in_array("Email", $salinanInformasi);
                $attachment = ($withAttachment && !empty($tanggapan->attachment_file_path))
                    ? $tanggapan->attachment_file_path
                    : null;
                    
                $this->emailService->sendTemplate('Jawaban Permohonan', $permohonan->pemohon->email, [
                    'tgl_diproses' => DateHelper::formatTanggalIndo($tanggapan->verified_at), 
                    'nama' => $permohonan->pemohon->name, 
                    'alamat' => $permohonan->pemohon->address, 
                    'no_telepon' => $permohonan->pemohon->phone_number, 
                    'email' => $permohonan->pemohon->email, 
                    'tanggapan' => $tanggapan->description, 
                    'no_permohonan' => $permohonan->number, 
                    'tgl_dijawab' => DateHelper::formatTanggalIndo($tanggapan->answered_at),
                    'nama_institusi' => $namaInstitusi,
                    'tgl_permohonan' => DateHelper::formatTanggalIndo($permohonan->created_at), 
                ], 'PPID Kemlu – Tanggapan atas Permohonan Informasi Anda', $attachment);
            } else if ($statusBaru == Permohonan::STATUS['DITOLAK']) {
                $this->emailService->sendTemplate('Penolakan Permohonan', $permohonan->pemohon->email, [
                    'tgl_diproses' => DateHelper::formatTanggalIndo($tanggapan->verified_at), 
                    'no_permohonan' => $permohonan->number,  
                    'nama' => $permohonan->pemohon->name, 
                    'alamat' => $permohonan->pemohon->address, 
                    'no_telepon' => $permohonan->pemohon->phone_number, 
                    'email' => $permohonan->pemohon->email, 
                    'alasan_ditolak' => $tanggapan->reason_rejected, 
                    'tgl_ditolak' => DateHelper::formatTanggalIndo(now()),
                    'tgl_permohonan' => DateHelper::formatTanggalIndo($permohonan->created_at), 
                ], 'PPID Kemlu – Permohonan Informasi Anda Tidak Dapat Dipenuhi', $tanggapan->rejection_attachment_file_path);
            }

            return ApiResponse::success([
                'permohonan' => $permohonan->fresh(),
                'tanggapan' => $tanggapan,
            ], 'Berhasil menyimpan perubahan status permohonan');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function disposition(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $permohonan = Permohonan::findOrFail($id);
            if ($permohonan->status != Permohonan::STATUS['DIAJUKAN']) {
                DB::rollBack();
                return ApiResponse::error('Permohonan sudah tidak dalam tahap pengajuan dan institusi tujuan tidak dapat diubah lagi.', 422);
            }

            $request->validate([
                'institutions' => 'required|array',
            ]);

            $user = auth('admin')->user()->name;

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
                        'permohonan_id' => $permohonan->id,
                        'destination_institution_type' => $destinationInstitutionType,
                        'destination_institution_id' => $destinationInstitutionId,
                    ], 
                    ['created_by' => $user]
                );
                $arrDestinationInstitution[] = $destinationExists->id;
            }
            PermohonanDestinationInstitution::where('permohonan_id', $permohonan->id)->whereNotIn('id', $arrDestinationInstitution)->delete();

            $permohonan->updated_by = $user;
            $permohonan->save();

            activity('permohonan')
                ->performedOn($permohonan)
                ->causedBy(auth('admin')->user())
                ->log('Petugas memindahkan permohonan ke institusi tujuan lain');

            DB::commit();

            return ApiResponse::success([
                'permohonan' => $permohonan->fresh(),
            ], 'Berhasil menyimpan perubahan institusi tujuan');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function export(Request $request)
    {
        try {
            $query = Permohonan::with(['pemohon']);

            if ($request->filled('status')) {
                $statuses = is_array($request->status) ? $request->status : [$request->status];
                $query->where(function ($q) use ($statuses) {
                    $q->where(function ($q1) use ($statuses) {
                        $q1->doesntHave('institutions')
                        ->whereIn('status', $statuses);
                    })
                    ->orWhereHas('institutions', function ($q2) use ($statuses) {
                        $q2->whereIn('status', $statuses);
                    });

                });
            }

            if ($request->filled('date_from')) {
                $dateFrom = Carbon::createFromFormat('d-m-Y', $request->date_from)->format('Y-m-d');
                $query->whereDate('created_at', '>=', $dateFrom);
            }

            if ($request->filled('date_to')) {
                $dateTo = Carbon::createFromFormat('d-m-Y', $request->date_to)->format('Y-m-d');
                $query->whereDate('created_at', '<=', $dateTo);
            }

            $query->orderBy('created_at', 'desc');
            $data = $query->get();
    
            return Excel::download(new PermohonanExport($data), 'laporan_permohonan.xlsx');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateLateStatus()
    {
        DB::beginTransaction();
        try {
            // ambil hanya yang sedang diproses
            $institutions = PermohonanDestinationInstitution::where('status', 1)->get();

            foreach ($institutions as $inst) {
                $due = $inst->due_date_response ? Carbon::parse($inst->due_date_response) : null;

                $respondedAt = $inst->tanggapan?->answered_at ? Carbon::parse($inst->tanggapan->answered_at) : ($inst->tanggapan?->rejected_at ? Carbon::parse($inst->tanggapan->rejected_at) : null);

                $isLate = false;

                if ($due) {
                    if (!$respondedAt) {
                        if (now()->greaterThan($due)) {
                            $isLate = true;
                        }
                    } else {
                        if ($respondedAt->greaterThan($due)) {
                            $isLate = true;
                        }
                    }
                }

                $inst->update([
                    'is_late_response' => $isLate,
                ]);
            }

            DB::commit();
            return ApiResponse::success('', 'Status keterlambatan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function additionalTime(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'reason_need_additional_time' => 'required|string',
                'permohonan_destination_institution_id' => 'required',
            ]);

            $destination = PermohonanDestinationInstitution::findOrFail($request->permohonan_destination_institution_id);

            if ($destination->is_need_additional_time) {
                DB::rollBack();
                return ApiResponse::error('Permohonan ini sudah pernah diperpanjang sebelumnya', '', 422);
            }
            if ($destination->status != Permohonan::STATUS['DIPROSES']) {
                DB::rollBack();
                return ApiResponse::error('Status permohonan tidak valid untuk perpanjangan waktu', '', 422);
            }
            if (!Carbon::parse($destination->due_date_response)->lessThanOrEqualTo(Carbon::today())) {
                DB::rollBack();
                return ApiResponse::error('Perpanjangan waktu hanya dapat dilakukan pada tanggal jatuh tempo atau setelahnya', '', 422);
            }

            $destination->is_need_additional_time = true;
            $destination->reason_need_additional_time = $request->reason_need_additional_time;
            $destination->additional_time_days = Permohonan::JUMLAH_HARI_TAMBAHAN;
            $destination->due_date_extended = DateHelper::addWorkingDays($destination->due_date_response, Permohonan::JUMLAH_HARI_TAMBAHAN);
            $destination->save();

            $namaInstitusi = $destination->name ?? '-';
            $permohonan = $destination->permohonan;

            activity('permohonan')
                ->performedOn($permohonan)
                ->causedBy(auth('admin')->user())
                ->log("Petugas dari {$namaInstitusi} telah menetapkan perpanjangan waktu proses penyediaan data/informasi");

            DB::commit();

            $this->emailService->sendTemplate('Tambahan Waktu Proses Permohonan', $permohonan->pemohon->email, [
                'tgl_diproses' => DateHelper::formatTanggalIndo($destination->tanggapan->verified_at),
                'no_permohonan' => $permohonan->number,  
                'nama' => $permohonan->pemohon->name, 
                'alamat' => $permohonan->pemohon->address, 
                'no_telepon' => $permohonan->pemohon->phone_number, 
                'email' => $permohonan->pemohon->email, 
                'alasan_penambahan_waktu' => $destination->reason_need_additional_time, 
                'tgl_dikirim' => DateHelper::formatTanggalIndo(now()),
                'nama_institusi' => $namaInstitusi,
                'jumlah_penambahan_hari' => $destination->additional_time_days,
                'tgl_permohonan' => DateHelper::formatTanggalIndo($permohonan->created_at)
            ], 'PPID Kemlu – Informasi Perpanjangan Waktu Proses Permohonan');

            return ApiResponse::success('', 'Berhasil menambahkan waktu proses permohonan');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = Permohonan::findOrFail($id);
            $oldPermohonan = $data;
            $data->delete();

            LogActivity::create('permohonan admin', $oldPermohonan, 'delete', auth('admin')->user());

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus permohonan');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
