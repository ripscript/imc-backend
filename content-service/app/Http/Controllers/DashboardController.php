<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function jumlahPerangkat(Request $request)
    {
        try {
            $user = auth()->user();
            $role = $user->role->name;
            if ($role == 'Admin Satker/Perwakilan') {
                return ApiResponse::error('Anda tidak memiliki akses', '', 401);
            }

            $query = DB::table('visitors')
                ->select('device_type', DB::raw('COUNT(*) as visits'))
                ->groupBy('device_type');

            // Filter tanggal jika ada
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            } elseif ($request->filled('start_date')) {
                $query->where('created_at', '>=', $request->start_date);
            } elseif ($request->filled('end_date')) {
                $query->where('created_at', '<=', $request->end_date);
            }

            $countByDeviceType = $query->get()->keyBy(fn($row) => strtolower($row->device_type));

            $totalVisits = $countByDeviceType->sum('visits');

            $mobileVisits = $countByDeviceType->get('mobile')->visits ?? 0;
            $desktopVisits = $countByDeviceType->get('desktop')->visits ?? 0;

            $data = [
                'Mobile' => $totalVisits > 0 ? round(($mobileVisits / $totalVisits) * 100, 2) : 0,
                'Desktop' => $totalVisits > 0 ? round(($desktopVisits / $totalVisits) * 100, 2) : 0,
            ];

            return ApiResponse::success($data, 'Berhasil mengambil data jumlah perangkat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function grafikPengunjung(Request $request)
    {
        try {
            $user = auth()->user();
            $role = $user->role->name;
            if ($role == 'Admin Satker/Perwakilan') {
                return ApiResponse::error('Anda tidak memiliki akses', '', 401);
            }
            
            $year = $request->input('year', date('Y'));

            // Data grafik per bulan
            $visitorsPerMonth = DB::table('visitors')
                ->select(DB::raw('EXTRACT(MONTH FROM created_at) AS month'), DB::raw('COUNT(*) AS total'))
                ->whereYear('created_at', $year)
                ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
                ->orderBy('month')
                ->pluck('total', 'month')
                ->toArray();

            $graphData = [];
            for ($i = 1; $i <= 12; $i++) {
                $graphData[$i] = $visitorsPerMonth[$i] ?? 0;
            }

            // Data untuk card
            $today = now()->format('Y-m-d');
            $monthStart = now()->startOfMonth()->format('Y-m-d');
            $monthEnd = now()->endOfMonth()->format('Y-m-d');

            $totalToday = DB::table('visitors')->whereDate('created_at', $today)->count();
            $totalThisMonth = DB::table('visitors')
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->count();
            $totalOverall = DB::table('visitors')->count();

            $cardData = [
                'hari_ini' => $totalToday,
                'bulan_ini' => $totalThisMonth,
                'keseluruhan' => $totalOverall,
            ];

            return ApiResponse::success([
                'graph' => $graphData,
                'card' => $cardData,
            ], 'Berhasil mengambil data pengunjung');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

}
