<?php

namespace App\Exports;

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

class JumlahPengunjungSheet implements WithEvents, WithTitle
{
    use Exportable;

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->setCellValue('A1', 'Jumlah Pengunjung');
                $sheet->getStyle("A1")->getFont()->setBold(true);

                $currentYear = Carbon::now()->year;
                $years = range($currentYear - 2, $currentYear);

                $sheet->setCellValue('A3', 'Bulan');
                $colYear = 'B';
                foreach ($years as $i => $y) {
                    $sheet->setCellValue("{$colYear}3", $y);

                    if ($i < count($years)-1) {
                        $colYear++;
                    }
                }
                $this->styleHeader($sheet, "A3:{$colYear}3");

                $row = 4;
                $monthNames = [
                    1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni',
                    7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
                ];

                $startCol = 'B';
                foreach ($monthNames as $month => $name) {
                    $sheet->setCellValue("A{$row}", $name);

                    $col = $startCol;
                    foreach ($years as $y) {
                        $totalVisitors = DB::table('visitors')
                            ->select(DB::raw('COUNT(*) AS total'))
                            ->whereYear('created_at', $y)
                            ->whereMonth('created_at', $month)
                            ->value('total') ?? 0;

                        $sheet->setCellValue("{$col}{$row}", $totalVisitors);

                        if ($y !== end($years)) {
                            $col++;
                        }
                    }
                    $row++;
                }

                $lastCol = $colYear;
                $sheet->getStyle("A3:{$lastCol}".($row-1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN
                        ],
                    ]
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
            ->getStartColor()->setARGB('FFF4A460');
    }

    public function title(): string
    {
        return 'Jumlah Pengunjung';
    }
}
