<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KeterlambatanSLASheet implements WithEvents, WithTitle
{
    use Exportable;

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Satuan Kerja
                $dataSatker = DB::table('permohonan_destination_institutions as pdi')
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

                $sheet->setCellValue('A1', '5 Unit Pusat dengan SLA Terlambat');
                $sheet->getStyle("A1")->getFont()->setBold(true);
                
                $sheet->setCellValue('A3', 'No.');
                $sheet->setCellValue('B3', 'Nama Unit');
                $sheet->setCellValue('C3', 'Jumlah Terlambat');
                
                $this->styleHeader($sheet, "A3:C3");
                $row = 4;
                foreach ($dataSatker as $i => $satker) {
                    $sheet->setCellValue("A{$row}", ($i+1));
                    $sheet->setCellValue("B{$row}", $satker->name);
                    $sheet->setCellValue("C{$row}", $satker->jumlah_terlambat);

                    if ($satker !== end($dataSatker)) {
                        $row++;
                    }
                }

                $sheet->getStyle("A3:C".($row-1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Perwakilan
                $dataPerwakilan = DB::table('permohonan_destination_institutions as pdi')
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

                $sheet->setCellValue('G1', '5 Perwakilan dengan SLA Terlambat');
                $sheet->getStyle("G1")->getFont()->setBold(true);
                
                $sheet->setCellValue('G3', 'No.');
                $sheet->setCellValue('H3', 'Nama Perwakilan');
                $sheet->setCellValue('I3', 'Jumlah Terlambat');
                
                $this->styleHeader($sheet, "G3:I3");
                $row = 4;
                foreach ($dataPerwakilan as $i => $perwakilan) {
                    $sheet->setCellValue("G{$row}", ($i+1));
                    $sheet->setCellValue("H{$row}", $perwakilan->name);
                    $sheet->setCellValue("I{$row}", $perwakilan->jumlah_terlambat);

                    if ($satker !== end($dataSatker)) {
                        $row++;
                    }
                }

                $sheet->getStyle("G3:I".($row-1))->applyFromArray([
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
            ->getStartColor()->setARGB('FFADFF2F');
    }

    public function title(): string
    {
        return 'SLA Terlambat';
    }
}
