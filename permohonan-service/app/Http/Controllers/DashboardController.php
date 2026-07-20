<?php

namespace App\Http\Controllers;

use App\Exports\DashboardExport;
use App\Helpers\ApiResponse;
use App\Helpers\DateHelper;
use App\Models\Keberatan;
use App\Models\MsSatuanKerja;
use App\Models\Permohonan;
use App\Models\PermohonanDestinationInstitution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function jumlahPermohonanKeberatan()
    {
        try {
            $data = [];
            $user = auth('admin')->user();

            // Permohonan
            $totalPermohonan = Permohonan::query()
                ->filterByRole($user)
                ->count();

            $permohonanBelumDiproses = Permohonan::where('status', 0)
                ->filterByRole($user)
                ->count();

            $permohonanDiproses = Permohonan::where('status', 1)
                ->filterByRole($user)
                ->count();

            $permohonanDijawab = Permohonan::where('status', 2)
                ->filterByRole($user)
                ->count();

            $permohonanDitolak = Permohonan::where('status', 3)
                ->filterByRole($user)
                ->count();

            $permohonanSelesai = Permohonan::whereIn('status', [2, 3])
                ->filterByRole($user)
                ->count();

            $data['total_permohonan'] = $totalPermohonan;
            $data['permohonan_belum_diproses'] = [
                'count' => $permohonanBelumDiproses,
                'percentage' => $totalPermohonan > 0 ? round(($permohonanBelumDiproses / $totalPermohonan) * 100, 2) : 0,
            ];
            $data['permohonan_diproses'] = [
                'count' => $permohonanDiproses,
                'percentage' => $totalPermohonan > 0 ? round(($permohonanDiproses / $totalPermohonan) * 100, 2) : 0,
            ];
            $data['permohonan_dijawab'] = [
                'count' => $permohonanDijawab,
                'percentage' => $totalPermohonan > 0 ? round(($permohonanDijawab / $totalPermohonan) * 100, 2) : 0,
            ];
            $data['permohonan_ditolak'] = [
                'count' => $permohonanDitolak,
                'percentage' => $totalPermohonan > 0 ? round(($permohonanDitolak / $totalPermohonan) * 100, 2) : 0,
            ];
            $data['permohonan_selesai'] = [
                'count' => $permohonanSelesai,
                'percentage' => $totalPermohonan > 0 ? round(($permohonanSelesai / $totalPermohonan) * 100, 2) : 0,
            ];

            // Keberatan
            $totalKeberatan = Keberatan::query()
                ->filterByRole($user)
                ->count();

            $keberatanDiterima = Keberatan::where('status', 1)
                ->filterByRole($user)
                ->count();

            $keberatanSelesai = Keberatan::where('status', 2)
                ->filterByRole($user)
                ->count();

            $data['total_keberatan'] = $totalKeberatan;
            $data['keberatan_diterima'] = [
                'count' => $keberatanDiterima,
                'percentage' => $totalKeberatan > 0 ? round(($keberatanDiterima / $totalKeberatan) * 100, 2) : 0,
            ];
            $data['keberatan_selesai'] = [
                'count' => $keberatanSelesai,
                'percentage' => $totalKeberatan > 0 ? round(($keberatanSelesai / $totalKeberatan) * 100, 2) : 0,
            ];

            return ApiResponse::success($data, 'Berhasil mengambil data ringkasan permohonan dan keberatan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function grafikPermohonanPerBulan(Request $request)
    {
        try {
            $user = auth('admin')->user();
            $year = $request->input('year', date('Y'));

            // Permohonan masuk per bulan
            $masukPerBulan = Permohonan::select(
                    DB::raw('EXTRACT(MONTH FROM created_at) AS month'),
                    DB::raw('COUNT(*) AS total')
                )
                ->filterByRole($user)
                ->whereYear('created_at', $year)
                ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
                ->pluck('total', 'month')
                ->toArray();

            // Permohonan selesai per bulan (status 2 atau 3)
            $selesaiPerBulan = Permohonan::select(
                    DB::raw('EXTRACT(MONTH FROM created_at) AS month'),
                    DB::raw('COUNT(*) AS total')
                )
                ->filterByRole($user)
                ->whereYear('created_at', $year)
                ->whereIn('status', [2,3])
                ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
                ->pluck('total', 'month')
                ->toArray();

            $graphData = [];
            for ($i = 1; $i <= 12; $i++) {
                $graphData[$i] = [
                    'masuk' => $masukPerBulan[$i] ?? 0,
                    'selesai' => $selesaiPerBulan[$i] ?? 0,
                ];
            }

            return ApiResponse::success([
                'year' => $year,
                'data' => $graphData
            ], 'Berhasil mengambil data permohonan per bulan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function grafikPermohonanByTipePemohon(Request $request)
    {
        try {
            $user = auth('admin')->user();
            $year = $request->input('year', date('Y'));

            $classifications = DB::table('ms_classification_pemohon')
                ->select('id', 'name')
                ->get();
    
            $data = [];
    
            foreach ($classifications as $class) {
                // Jumlah pemohon dengan klasifikasi ini
                $totalPemohon = DB::table('pemohon')
                    ->whereYear('created_at', $year)
                    ->where('classification_pemohon_id', $class->id)
                    ->count();
    
                // Ambil semua pemohon ID dari klasifikasi ini
                $pemohonIds = DB::table('pemohon')
                    ->where('classification_pemohon_id', $class->id)
                    ->pluck('id');
    
                // Jumlah permohonan untuk pemohon ini
                $totalPermohonan = Permohonan::whereYear('created_at', $year)
                    ->filterByRole($user)
                    ->whereIn('pemohon_id', $pemohonIds)
                    ->count();
    
                // Jumlah permohonan diproses
                $permohonanDiproses = Permohonan::whereYear('created_at', $year)
                    ->whereIn('pemohon_id', $pemohonIds)
                    ->filterByRole($user)
                    ->where('status', 1)
                    ->count();
    
                // Jumlah permohonan masih diajukan
                $permohonanBelumDijawab = Permohonan::whereYear('created_at', $year)
                    ->filterByRole($user)
                    ->whereIn('pemohon_id', $pemohonIds)
                    ->where('status', 0)
                    ->count();
    
                $data[] = [
                    'classification' => $class->name,
                    'total_pemohon' => $totalPemohon,
                    'total_permohonan' => $totalPermohonan,
                    'permohonan_diproses' => $permohonanDiproses,
                    'permohonan_belum_dijawab' => $permohonanBelumDijawab,
                ];
            }
    
            return ApiResponse::success($data, 'Berhasil mengambil data permohonan berdasarkan tipe pemohon');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function satkerSlaTerlambat()
    {
        try {
            $user = auth('admin')->user();
            $role = $user->role->name;
            if ($role == 'Admin Satker/Perwakilan') {
                return ApiResponse::error('Anda tidak memiliki akses', '', 401);
            }

            $data = DB::table('permohonan_destination_institutions as pdi')
                ->select(
                    'pdi.destination_institution_id',
                    'sk.name',
                    DB::raw('COUNT(*) as jumlah_terlambat')
                )
                ->join('ms_satuan_kerja as sk', 'pdi.destination_institution_id', '=', 'sk.id')
                ->where('pdi.destination_institution_type', 'App\\Models\\MsSatuanKerja')
                ->where('pdi.is_late_response', true)
                ->groupBy('pdi.destination_institution_id', 'sk.name')
                ->orderByDesc('jumlah_terlambat')
                ->take(5)
                ->get();

            return ApiResponse::success($data, 'Berhasil mengambil data satker dengan SLA terlambat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function perwakilanSlaTerlambat()
    {
        try {
            $user = auth('admin')->user();
            $role = $user->role->name;
            if ($role == 'Admin Satker/Perwakilan') {
                return ApiResponse::error('Anda tidak memiliki akses', '', 401);
            }

            $data = DB::table('permohonan_destination_institutions as pdi')
                ->select(
                    'pdi.destination_institution_id',
                    'p.full_name as name',
                    DB::raw('COUNT(*) as jumlah_terlambat')
                )
                ->join('ms_perwakilan as p', 'pdi.destination_institution_id', '=', 'p.id')
                ->where('pdi.destination_institution_type', 'App\\Models\\MsPerwakilan')
                ->where('pdi.is_late_response', true)
                ->groupBy('pdi.destination_institution_id', 'p.full_name')
                ->orderByDesc('jumlah_terlambat')
                ->take(5)
                ->get();

            return ApiResponse::success($data, 'Berhasil mengambil data perwakilan dengan SLA terlambat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function permohonanAkanKadaluwarsa(Request $request)
    {
        try {
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
                ->select(
                    'pm.id',
                    'pm.number',
                    'pm.detail',
                    'pm.status',
                    'pm.created_at',
                    'pm.due_date_response',
                    // DB::raw("string_agg(DISTINCT COALESCE(sk.name, pw.full_name), ',') as destination_institution_name")
                    DB::raw("
                        json_agg(
                            json_build_object(
                                'name', COALESCE(sk.name, pw.full_name),
                                'status', pdi.status,
                                'due_date_response', pdi.due_date_response
                            )
                        ) as destination_institutions
                    ")
                )
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
            
            $query
                ->where('pm.status', Permohonan::STATUS['DIPROSES'])
                ->whereDate('pdi.due_date_response', '>=', now()) // masih belum kadaluwarsa
                ->whereDate('pdi.due_date_response', '<=', now()->addDays(7)); // akan kadaluwarsa 7 hari lagi
            $query->orderBy('pm.created_at', 'desc');
            $query->groupBy('pm.id', 'pm.number', 'pm.detail', 'pm.status', 'pm.created_at', 'pm.due_date_response');

            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['pm.number', 'pm.detail'], null, 'pm.updated_at');

            $data['data'] = collect($data['data'])->map(function ($item) {
                $item->remaining_days = DateHelper::remainingDays($item->due_date_response, true) ?? null;
                    
                // $names = explode(',', $item->destination_institution_name ?? '');
                // $item->destination_institution_name = array_values(array_filter($names, fn($n) => !empty($n)));

                $institutions = json_decode($item->destination_institutions, true) ?? [];
                $item->destination_institutions = array_values(array_filter(array_map(function ($row) {
                    if (!empty($row['due_date_response']) && $row['status'] == Permohonan::STATUS['DIPROSES']) {
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

            return ApiResponse::success($data, 'Berhasil mengambil data permohonan yang akan kadaluwarsa dalam 7 hari');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function jumlahPermohonanAkanKadaluwarsa(Request $request)
    {
        try {
            $query = DB::table('permohonan_destination_institutions as pdi')
                ->leftJoin('ms_satuan_kerja as sk', function($join) {
                    $join->on('sk.id', '=', 'pdi.destination_institution_id')
                        ->where('pdi.destination_institution_type', 'App\\Models\\MsSatuanKerja');
                })
                ->leftJoin('ms_perwakilan as pw', function($join) {
                    $join->on('pw.id', '=', 'pdi.destination_institution_id')
                        ->where('pdi.destination_institution_type', 'App\\Models\\MsPerwakilan');
                })
                ->select(
                    'pdi.destination_institution_id',
                    'pdi.destination_institution_type',
                    DB::raw('COALESCE(sk.name, pw.full_name) as name'),
                    DB::raw('COUNT(pdi.id) as total_permohonan_akan_kadaluwarsa')
                )
                ->where('pdi.status', Permohonan::STATUS['DIPROSES'])
                ->where(function ($q) {
                    $q->whereDate('pdi.due_date_response', '<', now()) // expired
                    ->orWhereBetween('pdi.due_date_response', [now(), now()->addDays(3)]); // upcoming 3 days
                })
                ->where('is_need_additional_time', '!=', true)
                ->groupBy('pdi.destination_institution_id', 'pdi.destination_institution_type', 'sk.name', 'pw.full_name', 'pdi.updated_at');
            
            $user = auth('admin')->user();
            $role = $user->role->name;
            if ($role == 'Admin Satker/Perwakilan') {
                if ($user->perwakilan_id) {
                    $query->where('pdi.destination_institution_type', 'App\\Models\\MsPerwakilan')
                        ->where('pdi.destination_institution_id', $user->perwakilan_id);
                } elseif ($user->satuan_kerja_id) {
                    $query->where('pdi.destination_institution_type', 'App\\Models\\MsSatuanKerja')
                        ->where('pdi.destination_institution_id', $user->satuan_kerja_id);
                }
            }
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, [DB::raw('COALESCE(sk.name, pw.full_name)'), 'COUNT(pdi.id)'], null, 'pdi.updated_at');

            return ApiResponse::success($data, 'Berhasil mengambil data jumlah permohonan yang telah/akan kadaluwarsa dalam 3 hari');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function export()
    {
        try {
            return Excel::download(new DashboardExport(), 'dashboard_'.date('Y').'.xlsx');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function permohonanDenganTambahanWaktu(Request $request)
    {
        try {
            $query = DB::table('permohonan_destination_institutions as pdi')
                ->leftJoin('ms_satuan_kerja as sk', function($join) {
                    $join->on('sk.id', '=', 'pdi.destination_institution_id')
                        ->where('pdi.destination_institution_type', 'App\\Models\\MsSatuanKerja');
                })
                ->leftJoin('ms_perwakilan as pw', function($join) {
                    $join->on('pw.id', '=', 'pdi.destination_institution_id')
                        ->where('pdi.destination_institution_type', 'App\\Models\\MsPerwakilan');
                })
                ->join('permohonan as pm','pdi.permohonan_id', '=', 'pm.id')
                ->select(
                    'pm.id',
                    'pm.number',
                    'pm.detail',
                    'pm.status',
                    'pdi.destination_institution_id',
                    'pdi.destination_institution_type',
                    DB::raw('COALESCE(sk.name, pw.full_name) as institution_name'),
                    'pdi.due_date_extended'
                )
                ->where('pdi.status', Permohonan::STATUS['DIPROSES'])
                ->where('pdi.is_need_additional_time', true);
            
            $user = auth('admin')->user();
            $role = $user->role->name;
            if ($role == 'Admin Satker/Perwakilan') {
                if ($user->perwakilan_id) {
                    $query->where('pdi.destination_institution_type', 'App\\Models\\MsPerwakilan')
                        ->where('pdi.destination_institution_id', $user->perwakilan_id);
                } elseif ($user->satuan_kerja_id) {
                    $query->where('pdi.destination_institution_type', 'App\\Models\\MsSatuanKerja')
                        ->where('pdi.destination_institution_id', $user->satuan_kerja_id);
                }
            }
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, [DB::raw('COALESCE(sk.name, pw.full_name)'), 'pm.number', 'pm.detail'], null, 'pdi.updated_at');

            $data['data'] = collect($data['data'])->map(function ($item) {
                $item->remaining_days = DateHelper::remainingDays($item->due_date_extended, true) ?? null;
                return $item;
            })->all();

            return ApiResponse::success($data, 'Berhasil mengambil data jumlah permohonan yang memerlukan tambahan waktu');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
