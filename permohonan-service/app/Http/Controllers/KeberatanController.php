<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\DateHelper;
use App\Models\Keberatan;
use App\Models\KeberatanAlasan;
use App\Models\MsAlasanKeberatan;
use App\Models\Permohonan;
use App\Traits\AuthorizesPemohon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeberatanController extends Controller
{
    use AuthorizesPemohon;

    public function index(Request $request)
    {
        try {
            $pemohonId = auth('pemohon')->id();
            $query = DB::table('keberatan as k')
                ->join('permohonan as pm', 'k.permohonan_id', '=', 'pm.id')
                ->select(
                    'k.number',
                    'pm.number as permohonan_number',
                    'k.detail',
                    'k.status',
                    'k.created_at',
                )
                ->where('k.pemohon_id', $pemohonId)
                ->whereNull('k.deleted_at')
                ->orderBy('k.created_at', 'desc');
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['k.number', 'k.detail', 'pm.number'], null, 'k.updated_at');

            return ApiResponse::success($data, 'Berhasil mengambil data keberatan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'permohonan_number' => 'required',
                'detail' => 'required|string',
                'additional_document_file_path' => 'nullable|string',
                'alasan_keberatan_id' => 'required|array'
            ]);

            $permohonan = Permohonan::findByNumber($request->permohonan_number);
            $this->authorizePemohon($permohonan);
            if (!$permohonan) {
                DB::rollBack();
                return ApiResponse::error('Permohonan tidak ditemukan', '', 404);
            }
            if (!in_array($permohonan->status, [Permohonan::STATUS['DIJAWAB'], Permohonan::STATUS['DITOLAK']])) {
                DB::rollBack();
                return ApiResponse::error('Tidak bisa mengajukan keberatan karena permohonan belum selesai diproses', '', 422);
            }

            if ($permohonan->keberatan) {
                DB::rollBack();
                return ApiResponse::error('Keberatan sudah diajukan sebelumnya', '', 422);
            }

            $data = new Keberatan();
            $data->permohonan_id = $permohonan->id;
            $data->pemohon_id = auth('pemohon')->id();
            $data->detail = $request->detail;
            $data->additional_document_file_path = $request->additional_document_file_path;
            $data->status = 0;
            $data->created_by = auth('pemohon')->user()->name;
            $data->save();

            if ($request->filled('alasan_keberatan_id')) {
                $totalAlasanValid = MsAlasanKeberatan::whereIn('id', $request->alasan_keberatan_id)->active()->count();
                if ($totalAlasanValid !== count($request->alasan_keberatan_id)) {
                    DB::rollBack();
                    return ApiResponse::error('Terdapat alasan keberatan data yang tidak valid', '', 422);
                }

                foreach ($request->alasan_keberatan_id as $value) {
                    KeberatanAlasan::create([
                        'keberatan_id' => $data->id,
                        'alasan_keberatan_id' => $value
                    ]);
                }
            }

            activity('permohonan')
            ->performedOn($permohonan)
            ->causedBy(auth('pemohon')->user())
            ->log('Pemohon menolak hasil jawaban');

            activity('keberatan')
            ->performedOn($data)
            ->causedBy(auth('pemohon')->user())
            ->log('Keberatan diajukan oleh pemohon');
            
            DB::commit();
            return ApiResponse::success('', 'Berhasil membuat keberatan dengan nomor '.$data->number, 201);
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

            $keberatan = Keberatan::findByNumber($request->number);

            $permohonan = $keberatan->permohonan;
            $permohonan->akses_data_name = $permohonan->aksesData->pluck('master.name');
            $permohonan->format_data_name = $permohonan->formatData->pluck('master.name');
            $permohonan->salinan_informasi_name = $permohonan->salinanInformasi->pluck('master.name');
            $permohonan->makeHidden(['id', 'pemohon_id', 'created_by', 'updated_by', 'deleted_at', 'destinationInstitution', 'aksesData', 'formatData', 'salinanInformasi']);
            $keberatan->setRelation('permohonan', $permohonan);

            $tanggapan = $permohonan->tanggapan;
            $tanggapan->makeHidden(['id', 'permohonan_id', 'created_at', 'updated_at', 'verified_by', 'verified_at', 'rejected_by', 'rejected_at', 'answered_by', 'answered_at']);
            $permohonan->setRelation('tanggapan', $tanggapan);

            $this->authorizePemohon($permohonan);

            $keberatan->alasan_keberatan_id = $keberatan->alasanKeberatan->pluck('alasan_keberatan_id');
            $keberatan->alasan_keberatan_name = $keberatan->alasanKeberatan->pluck('master.name');
            $keberatan->makeHidden(['id', 'pemohon_id', 'permohonan_id', 'created_by', 'updated_by', 'deleted_at', 'alasanKeberatan']);

            if ($keberatan->status == Keberatan::STATUS['DIJAWAB']) {
                $tanggapanKeberatan = $keberatan->tanggapan;

                if ($tanggapanKeberatan) {
                    $tanggapanKeberatan->makeHidden(['id', 'keberatan_id', 'verified_by', 'verified_at', 'answered_by', 'answered_at']);
                    $keberatan->setRelation('tanggapan', $tanggapanKeberatan);
                }
            }

            return ApiResponse::success($keberatan, 'Berhasil mengambil detail keberatan permohonan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'number' => 'required',
                'detail' => 'required|string',
                'additional_document_file_path' => 'nullable|string',
                'alasan_keberatan_id' => 'required|array'
            ]);

            $data = Keberatan::findByNumber($request->number);
            $this->authorizePemohon($data->permohonan);
            if ($data->status != Keberatan::STATUS['DIAJUKAN']) {
                DB::rollBack();
                return ApiResponse::error('Keberatan dalam proses atau yang sudah selesai tidak dapat diubah', '', 422);
            }

            $data->detail = $request->detail;
            $data->additional_document_file_path = $request->additional_document_file_path;
            $data->updated_by = auth('pemohon')->user()->name;
            $data->save();

            if ($request->filled('alasan_keberatan_id')) {
                $totalAlasanValid = MsAlasanKeberatan::whereIn('id', $request->alasan_keberatan_id)->active()->count();
                if ($totalAlasanValid !== count($request->alasan_keberatan_id)) {
                    DB::rollBack();
                    return ApiResponse::error('Terdapat alasan keberatan data yang tidak valid', '', 422);
                }

                foreach ($request->alasan_keberatan_id as $value) {
                    KeberatanAlasan::firstOrCreate([
                        'keberatan_id' => $data->id,
                        'alasan_keberatan_id' => $value
                    ]);
                }
                KeberatanAlasan::where('keberatan_id', $data->id)->whereNotIn('alasan_keberatan_id', $request->alasan_keberatan_id)->delete();
            } else {
                KeberatanAlasan::where('keberatan_id', $data->id)->delete();
            }

            activity('keberatan')
            ->performedOn($data)
            ->causedBy(auth('pemohon')->user())
            ->log('Keberatan diperbarui oleh pemohon');
            
            DB::commit();
            return ApiResponse::success('', 'Berhasil memperbarui keberatan dengan nomor '.$data->number);
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

            $keberatan = Keberatan::findByNumber($request->number);
            $this->authorizePemohon($keberatan->permohonan);
            if ($keberatan->status != Keberatan::STATUS['DIAJUKAN']) {
                DB::rollBack();
                return ApiResponse::error('Keberatan dalam proses atau yang sudah selesai tidak dapat dibatalkan', '', 422);
            }
            $number = $keberatan->number;
            $keberatan->delete();
            
            activity('keberatan')
            ->performedOn($keberatan)
            ->causedBy(auth('pemohon')->user())
            ->log('Keberatan dibatalkan oleh pemohon');

            DB::commit();
            return ApiResponse::success('', 'Berhasil membatalkan keberatan dengan nomor '.$number);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }  
}
