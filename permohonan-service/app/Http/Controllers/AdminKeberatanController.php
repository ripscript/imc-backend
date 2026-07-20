<?php

namespace App\Http\Controllers;

use App\Exports\KeberatanExport;
use App\Helpers\ApiResponse;
use App\Helpers\DateHelper;
use App\Models\Keberatan;
use App\Models\KeberatanTanggapan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AdminKeberatanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('keberatan as k')
                ->join('permohonan as pm', 'k.permohonan_id', '=', 'pm.id')
                ->select(
                    'k.id',
                    'k.number',
                    'pm.number as permohonan_number',
                    'k.detail',
                    'k.status',
                    'k.created_at',
                    'k.due_date_response'
                )
                ->whereNull('k.deleted_at');
            
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
                
            $query->orderBy('k.created_at', 'desc');
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['k.number', 'k.detail', 'pm.number'], null, 'k.updated_at');

            $data['data'] = $data['data']->map(function ($item) {
                $item->remaining_days = ($item->status == Keberatan::STATUS['DIPROSES']) ?
                    DateHelper::remainingDays($item->due_date_response, true) :
                    null;
                return $item;
            });

            return ApiResponse::success($data, 'Berhasil mengambil data keberatan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    
    public function detail($id) 
    {
        try {
            $keberatan = Keberatan::with([
                'pemohon',
                'alasanKeberatan.master',
            ])->findOrFail($id);

            $keberatan->alasan_keberatan_name = $keberatan->alasanKeberatan->pluck('master.name');
            $keberatan->remaining_days_due_date_response = ($keberatan->status == Keberatan::STATUS['DIPROSES']) ?
                DateHelper::remainingDays($keberatan->due_date_response, true) :
                null;

            $permohonan = $keberatan->permohonan;
            $permohonan->akses_data_name = $permohonan->aksesData->pluck('master.name');
            $permohonan->format_data_name = $permohonan->formatData->pluck('master.name');
            $permohonan->salinan_informasi_name = $permohonan->salinanInformasi->pluck('master.name');
            $permohonan->makeHidden(['aksesData', 'formatData', 'salinanInformasi']);
            $keberatan->setRelation('permohonan', $permohonan);
            
            $permohonan->tanggapan;
            $permohonan->institutions;

            if ($keberatan->status == Keberatan::STATUS['DIJAWAB']) {
                $keberatan->tanggapan;
            }

            $keberatan->makeHidden(['alasanKeberatan']);

            return ApiResponse::success($keberatan, 'Berhasil mengambil detail keberatan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'status' => 'required|in:1,2',
            ]);

            $keberatan = Keberatan::findOrFail($id);
            $user = auth('admin')->user()->name;
            $statusBaru = (int) $request->status;
            $statusLama = (int) $keberatan->status;

            if ($statusLama == Keberatan::STATUS['DIJAWAB']) {
                return ApiResponse::error('Keberatan sudah selesai dijawab dan tidak dapat diubah lagi.', 422);
            }

            $allowedNext = [
                Keberatan::STATUS['DIAJUKAN'] => [Keberatan::STATUS['DIPROSES']],
                Keberatan::STATUS['DIPROSES'] => [Keberatan::STATUS['DIJAWAB']],
            ];

            if (!isset($allowedNext[$statusLama]) || !in_array($statusBaru, $allowedNext[$statusLama])) {
                return ApiResponse::error('Perubahan status tidak valid atau tidak berurutan.', 422);
            }

            if ($statusBaru === Keberatan::STATUS['DIJAWAB']) {
                $request->validate([
                    'tanggapan.fpt_file_path' => 'nullable|string',
                    'tanggapan.response_letter_file_path' => 'nullable|string',
                    'tanggapan.description' => 'required|string',
                    'tanggapan.attachment_file_path' => 'nullable|string',
                ]);
            }

            $tanggapan = KeberatanTanggapan::firstOrCreate(['keberatan_id' => $id]);
            $requestTanggapan = $request->tanggapan;

            switch ($statusBaru) {
                case Keberatan::STATUS['DIPROSES']:
                    $keberatan->due_date_response = DateHelper::addWorkingDays(Carbon::now(), Keberatan::JUMLAH_HARI_PROSES);
                    $tanggapan->verified_by = $user;
                    $tanggapan->verified_at = now();
                    $logMessage = [
                        "Keberatan sedang diverifikasi oleh petugas"
                    ];
                break;

                case Keberatan::STATUS['DIJAWAB']:
                    if (empty($tanggapan->number)) {
                        $tanggapan->number = KeberatanTanggapan::generateNumber();
                    }
                    $tanggapan->fill([
                        'fpt_file_path' => $requestTanggapan['fpt_file_path'] ?? '',
                        'response_letter_file_path' => $requestTanggapan['response_letter_file_path'] ?? '',
                        'description' => $requestTanggapan['description'],
                        'attachment_file_path' => $requestTanggapan['attachment_file_path'] ?? '',
                        'answered_by' => $user,
                        'answered_at' => now(),
                    ]);

                    if ($keberatan->permohonan) {
                        $keberatan->permohonan->update([
                            'is_verified_by_pemohon' => true,
                        ]);
                    }

                    $logMessage = [
                        "Keberatan disetujui, data/informasi tambahan dikirim ke pemohon",
                        "Proses keberatan dinyatakan selesai"
                    ];
                break;
            }

            $tanggapan->save();

            $keberatan->updated_by = $user;
            $keberatan->status = $statusBaru;
            $keberatan->save();

            foreach ($logMessage as $value) {
                activity('keberatan')
                    ->performedOn($keberatan)
                    ->causedBy(auth('admin')->user())
                    ->log($value);
            }

            DB::commit();

            $alasanKeberatan = $keberatan->alasanKeberatan
                ->map(fn ($item) => $item->master->name ?? null)
                ->filter()
                ->values()
                ->toArray();
            if ($keberatan->status == Keberatan::STATUS['DIPROSES']) {
                $this->emailService->sendTemplate('Persetujuan Keberatan', $keberatan->pemohon->email, [
                    'tgl_keberatan' => DateHelper::formatTanggalIndo($keberatan->created_at), 
                    'no_keberatan' => $keberatan->number, 
                    'no_permohonan' => $keberatan->permohonan->number, 
                    'nama' => $keberatan->pemohon->name, 
                    'alamat' => $keberatan->pemohon->address, 
                    'no_telepon' => $keberatan->pemohon->phone_number, 
                    'email' => $keberatan->pemohon->email, 
                    'alasan_keberatan_pemohon' => implode(', ', $alasanKeberatan), 
                    'detail_keberatan_pemohon' => $keberatan->detail, 
                    'tgl_diproses' => DateHelper::formatTanggalIndo($tanggapan->verified_at)
                ], 'PPID Kemlu – Pengajuan Keberatan Anda Telah Diterima');
            } else if ($keberatan->status == Keberatan::STATUS['DIJAWAB']) {
                $this->emailService->sendTemplate('Tanggapan Keberatan', $keberatan->pemohon->email, [
                    'tgl_keberatan' => DateHelper::formatTanggalIndo($keberatan->created_at), 
                    'no_keberatan' => $keberatan->number, 
                    'no_permohonan' => $keberatan->permohonan->number, 
                    'nama' => $keberatan->pemohon->name, 
                    'alamat' => $keberatan->pemohon->address, 
                    'no_telepon' => $keberatan->pemohon->phone_number, 
                    'email' => $keberatan->pemohon->email, 
                    'tgl_dijawab' => DateHelper::formatTanggalIndo($tanggapan->answered_at), 
                    'tanggapan' => $tanggapan->description,
                    'alasan_keberatan_pemohon' => implode(', ', $alasanKeberatan), 
                    'detail_keberatan_pemohon' => $keberatan->detail
                ], 'PPID Kemlu – Tanggapan atas Keberatan Anda');
            }

            return ApiResponse::success([
                'keberatan' => $keberatan->fresh(),
                'tanggapan' => $tanggapan,
            ], 'Berhasil menyimpan perubahan status keberatan');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function export(Request $request)
    {
        try {
            $query = Keberatan::with(['permohonan']);

            if ($request->filled('status')) {
                $statuses = is_array($request->status) ? $request->status : [$request->status];
                $query->whereIn('status', $statuses);
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
    
            return Excel::download(new KeberatanExport($data), 'laporan_keberatan.xlsx');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
