<?php

namespace App\Exports;

use App\Helpers\DateHelper;
use App\Models\Permohonan;
use App\Models\PermohonanDestinationInstitution;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PermohonanAkanKadaluwarsaSheet implements WithEvents, WithTitle
{
    use Exportable;

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // List Permohonan
                $sheet->setCellValue('A1', 'Permohonan Dalam Proses yang Membutuhkan Tambahan Waktu');
                $sheet->getStyle("A1")->getFont()->setBold(true);
                
                $sheet->setCellValue('A3', 'No.');
                $sheet->setCellValue('B3', 'No Permohonan');
                $sheet->setCellValue('C3', 'Detail Permohonan');
                $sheet->setCellValue('D3', 'Satuan Kerja/Perwakilan');
                $sheet->setCellValue('E3', 'Status');
                $sheet->setCellValue('F3', 'Tenggat Tambahan Waktu Proses');
                
                $this->styleHeader($sheet, "A3:F3");

                $dataPermohonan = $this->dataPermohonanTambahanWaktu();
                $row = 4;
                foreach ($dataPermohonan as $i => $permohonan) {
                    $sheet->setCellValue("A{$row}", ($i+1));
                    $sheet->setCellValue("B{$row}", $permohonan->number);
                    $sheet->setCellValue("C{$row}", $permohonan->detail);
                    $sheet->setCellValue("D{$row}", $permohonan->institution_name);
                    $sheet->setCellValue("E{$row}", Permohonan::STATUS_TEXT[$permohonan->status]);
                    $sheet->setCellValue("F{$row}", DateHelper::formatTanggalIndo($permohonan->due_date_extended));

                    $row++;
                }
                
                $sheet->getStyle("A3:F".($row-1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Rekap Permohonan
                $sheet->setCellValue('J1', 'Rekap Permohonan Telah/Akan Kadaluwarsa dalam 3 Hari');
                $sheet->getStyle("J1")->getFont()->setBold(true);
                $sheet->setCellValue('J3', 'No.');
                $sheet->setCellValue('K3', 'Satuan Kerja/Perwakilan');
                $sheet->setCellValue('L3', 'Jumlah Permohonan');
                $this->styleHeader($sheet, "J3:L3");

                $rekapPermohonan = $this->rekapPermohonanAkanKadaluwarsa();
                $row = 4;
                foreach ($rekapPermohonan as $i => $rekap) {
                    $sheet->setCellValue("J{$row}", ($i+1));
                    $sheet->setCellValue("K{$row}", $rekap->name);
                    $sheet->setCellValue("L{$row}", $rekap->total_permohonan_akan_kadaluwarsa);

                    $row++;
                }

                $sheet->getStyle("J3:L".($row-1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            }
        ];
    }

    private function styleHeader($sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFDA70D6');
    }

    private function rekapPermohonanAkanKadaluwarsa()
    {
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
            ->groupBy('pdi.destination_institution_id', 'pdi.destination_institution_type', 'sk.name', 'pw.full_name');
        
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

        return $query->get();
    }

    private function dataPermohonanTambahanWaktu()
    {
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

        return $query->get();
            
    }

    public function title(): string
    {
        return 'Permohonan Akan Kadaluwarsa';
    }
}
