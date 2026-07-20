<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DateHelper
{
    const NAMA_BULAN = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    public static function addWorkingDays($startDate, $days)
    {
        $date = Carbon::parse($startDate);
        $addedDays = 0;

        while ($addedDays < $days) {
            $date->addDay();

            if (self::isWorkingDay($date)) {
                $addedDays++;
            }
        }

        return $date;
    }

    public static function isWorkingDay(Carbon $date)
    {
        // Jika Sabtu (6) atau Minggu (7) → bukan hari kerja
        if (in_array($date->dayOfWeekIso, [6, 7])) {
            return false;
        }

         // Jika ada di tabel hari libur nasional → bukan hari kerja
        if (DB::table('ms_holidays')->whereDate('date', $date)->exists()) {
            return false;
        }

        return true;
    }

    public static function remainingDays($dueDate, $workingDaysOnly = true, $startDate = null)
    {
        if (empty($dueDate)) {
            return null;
        }
    
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfDay();
        $due = Carbon::parse($dueDate)->startOfDay();

        if ($start->gte($due)) {
            return 0; // jika hari ini deadline atau sudah lewat -> 0
        }

        if (!$workingDaysOnly) {
            return $start->diffInDays($due);
        }

        // hitung hari kerja saja
        $remaining = 0;
        $date = $start->copy();
        while ($date->lt($due)) {
            if (self::isWorkingDay($date)) {
                $remaining++;
            }
            $date->addDay();
        }

        return $remaining;
    }

    public static function formatTanggalIndo($tanggal)
    {
        if (!$tanggal) {
            return '';
        }

        $date = Carbon::parse($tanggal);
        $tanggal = $date->format('d') . ' ' . self::NAMA_BULAN[$date->format('n')] . ' ' . $date->format('Y');

        return $tanggal;
    }
}
