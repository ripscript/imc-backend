<?php

namespace App\Exports;

use App\Models\Permohonan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PermohonanByTipePemohonSheet implements WithEvents, WithTitle
{
    use Exportable;

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $user = auth('admin')->user();

                $sheet->setCellValue('A1', 'Permohonan Berdasarkan Tipe Pemohon');
                $sheet->getStyle("A1")->getFont()->setBold(true);

                $sheet->setCellValue('A3', 'Tipe Pemohon');
                $sheet->setCellValue('B3', 'Kategori Data');
                $currentYear = Carbon::now()->year;
                $years = range($currentYear - 2, $currentYear);
                $colYear = "C";
                foreach ($years as $y) {
                    $sheet->setCellValue("{$colYear}3", $y);
                    if ($y !== end($years)) {
                        $colYear++;
                    }
                }
                $this->styleHeader($sheet, "A3:{$colYear}3");

                $classifications = DB::table('ms_classification_pemohon')
                    ->select('id', 'name')
                    ->get();
                $row = 4;
                foreach ($classifications as $class) {
                    $startRow = $row;
                    $sheet->setCellValue("A{$row}", $class->name);
                    $sheet->setCellValue("B{$row}", "Total Pemohon");
                    $rowTotalPemohon = $row;
                    $row++;
                    $sheet->setCellValue("B{$row}", "Total Permohonan");
                    $rowTotalPermohonan = $row;
                    $row++;
                    $sheet->setCellValue("B{$row}", "Permohonan Diproses");
                    $rowPermohonanDiproses = $row;
                    $row++;
                    $sheet->setCellValue("B{$row}", "Permohonan Belum Dijawab");
                    $rowPermohonanPending = $row;

                    $sheet->mergeCells("A{$startRow}:A{$row}");

                    $colYear = "C";
                    foreach ($years as $year) {
                        $totalPemohon = DB::table('pemohon')
                            ->whereYear('created_at', $year)
                            ->where('classification_pemohon_id', $class->id)
                            ->count();
        
                        $pemohonIds = DB::table('pemohon')
                            ->where('classification_pemohon_id', $class->id)
                            ->pluck('id');
    
                        $totalPermohonan = Permohonan::whereYear('created_at', $year)
                            ->filterByRole($user)
                            ->whereIn('pemohon_id', $pemohonIds)
                            ->count();
            
                        $permohonanDiproses = Permohonan::whereYear('created_at', $year)
                            ->whereIn('pemohon_id', $pemohonIds)
                            ->filterByRole($user)
                            ->where('status', 1)
                            ->count();
            
                        $permohonanBelumDijawab = Permohonan::whereYear('created_at', $year)
                            ->filterByRole($user)
                            ->whereIn('pemohon_id', $pemohonIds)
                            ->where('status', 0)
                            ->count();

                        $sheet->setCellValue("{$colYear}{$rowTotalPemohon}", $totalPemohon);
                        $sheet->setCellValue("{$colYear}{$rowTotalPermohonan}", $totalPermohonan);
                        $sheet->setCellValue("{$colYear}{$rowPermohonanDiproses}", $permohonanDiproses);
                        $sheet->setCellValue("{$colYear}{$rowPermohonanPending}", $permohonanBelumDijawab);

                        if ($year !== end($years)) {
                            $colYear++;
                        }
                    }

                    $row++;
                }

                $lastCol = $colYear;
                $sheet->getStyle("A3:{$lastCol}".($row-1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }

    private function styleHeader($sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF7FFFD4');
    }

    public function title(): string
    {
        return 'Permohonan Berdasarkan Tipe Pemohon';
    }
}
