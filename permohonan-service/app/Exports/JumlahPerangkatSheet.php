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

class JumlahPerangkatSheet implements WithEvents, WithTitle
{
    use Exportable;

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->setCellValue('A1', 'Aksesibilitas Perangkat');
                $sheet->getStyle("A1")->getFont()->setBold(true);

                $query = DB::table('visitors')
                    ->select('device_type', DB::raw('COUNT(*) as visits'))
                    ->groupBy('device_type');

                $countByDeviceType = $query->get()->keyBy(fn($row) => strtolower($row->device_type));

                $mobileVisits = $countByDeviceType->get('mobile')->visits ?? 0;
                $desktopVisits = $countByDeviceType->get('desktop')->visits ?? 0;
                
                $sheet->setCellValue('A3', 'Tipe Perangkat');
                $sheet->setCellValue('B3', 'Jumlah');
                $sheet->setCellValue('C3', 'Persentase');

                $this->styleHeader($sheet, "A3:C3");

                $mobileVisits = $countByDeviceType->get('mobile')->visits ?? 0;
                $desktopVisits = $countByDeviceType->get('desktop')->visits ?? 0;
                
                $sheet->setCellValue('A4', 'Mobile');
                $sheet->setCellValue('B4', $mobileVisits);
                $sheet->setCellValue('C4', '=IF((B4+B5)>0, B4/(B4+B5), 0)');

                $sheet->setCellValue('A5', 'Desktop');
                $sheet->setCellValue('B5', $desktopVisits);
                $sheet->setCellValue('C5', '=IF((B4+B5)>0, B5/(B4+B5), 0)');

                $sheet->getStyle("A3:C5")->applyFromArray([
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
            ->getStartColor()->setARGB('FFB0E0E6');
    }

    public function title(): string
    {
        return 'Aksesibilitas Perangkat';
    }
}
