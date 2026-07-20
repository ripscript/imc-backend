<?php

namespace App\Exports;

use App\Models\Permohonan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PermohonanPerBulanSheet implements WithEvents, WithTitle
{
    use Exportable;

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $user = auth('admin')->user();

                $sheet->setCellValue('A1', 'Permohonan Per Bulan');
                $sheet->getStyle("A1")->getFont()->setBold(true);

                $currentYear = Carbon::now()->year;
                $years = range($currentYear - 2, $currentYear);

                $sheet->setCellValue('A3', 'Bulan');
                $sheet->mergeCells("A3:A4");
                $colYear = 'B';
                foreach ($years as $i => $y) {
                    $startCol = $colYear;
                    $sheet->setCellValue("{$colYear}3", $y);
                    $sheet->setCellValue("{$colYear}4", 'Permohonan Masuk');
                    $colYear++;
                    $sheet->setCellValue("{$colYear}4", 'Permohonan Selesai');

                    $sheet->mergeCells("{$startCol}3:{$colYear}3");

                    if ($i < count($years)-1) {
                        $colYear++;
                    }
                }
                $this->styleHeader($sheet, "A3:{$colYear}4");

                $row = 5;
                $monthNames = [
                    1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni',
                    7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
                ];

                foreach (array_keys($monthNames) as $month) {
                    $sheet->setCellValue("A{$row}", $monthNames[$month]);

                    $colYear = 'B';
                    foreach ($years as $year) {

                        $masuk = Permohonan::filterByRole($user)
                            ->whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->count();

                        $selesai = Permohonan::filterByRole($user)
                            ->whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->whereIn('status', [2,3])
                            ->count();

                        $sheet->setCellValue("{$colYear}{$row}", $masuk);
                        $colYear++;
                        $sheet->setCellValue("{$colYear}{$row}", $selesai);

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
            ->getStartColor()->setARGB('FFFFC0CB');
    }

    public function title(): string
    {
        return 'Permohonan Per Bulan';
    }
}
