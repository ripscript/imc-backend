<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DashboardExport implements WithMultipleSheets
{
    public function sheets(): array
    {   
        $listSheet = [
            new JumlahPermohonanKeberatanSheet(),
            new PermohonanByTipePemohonSheet(),
            new PermohonanPerBulanSheet(),
            new PermohonanAkanKadaluwarsaSheet()
        ];
        
        if (auth('admin')->user()->role->name != 'Admin Satker/Perwakilan') {
            $listSheet = array_merge($listSheet, [
                new KeterlambatanSLASheet(),
                new JumlahPerangkatSheet(),
                new JumlahPengunjungSheet()
            ]);
        }

        return $listSheet;
    }
}
