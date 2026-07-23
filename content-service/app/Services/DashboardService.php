<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function jumlahPerangkat(array $data)
    {
        $user = auth()->user();
        $role = $user->role->name;
        if ($role == 'Admin Satker/Perwakilan') {
            throw new \Exception('Anda tidak memiliki akses', 401);
        }

        $query = DB::table('visitors')
            ->select('device_type', DB::raw('COUNT(*) as visits'))
            ->groupBy('device_type');

        if (! empty($data['start_date']) && ! empty($data['end_date'])) {
            $query->whereBetween('created_at', [$data['start_date'], $data['end_date']]);
        } elseif (! empty($data['start_date'])) {
            $query->where('created_at', '>=', $data['start_date']);
        } elseif (! empty($data['end_date'])) {
            $query->where('created_at', '<=', $data['end_date']);
        }

        $countByDeviceType = $query->get()->keyBy(fn ($row) => strtolower($row->device_type));
        $totalVisits = $countByDeviceType->sum('visits');
        $mobileVisits = $countByDeviceType->get('mobile')->visits ?? 0;
        $desktopVisits = $countByDeviceType->get('desktop')->visits ?? 0;

        return [
            'Mobile' => $totalVisits > 0 ? round(($mobileVisits / $totalVisits) * 100, 2) : 0,
            'Desktop' => $totalVisits > 0 ? round(($desktopVisits / $totalVisits) * 100, 2) : 0,
        ];
    }

    public function grafikPengunjung(array $data)
    {
        $user = auth()->user();
        $role = $user->role->name;
        if ($role == 'Admin Satker/Perwakilan') {
            throw new \Exception('Anda tidak memiliki akses', 401);
        }

        $year = $data['year'] ?? date('Y');

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

        $totalToday = DB::table('visitors')->whereDate('created_at', now()->format('Y-m-d'))->count();
        $totalThisMonth = DB::table('visitors')
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->count();
        $totalOverall = DB::table('visitors')->count();

        return [
            'graph' => $graphData,
            'card' => [
                'hari_ini' => $totalToday,
                'bulan_ini' => $totalThisMonth,
                'keseluruhan' => $totalOverall,
            ],
        ];
    }
}
