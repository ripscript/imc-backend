<?php

namespace App\Exports;

use App\Models\Keberatan;
use App\Models\Permohonan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class JumlahPermohonanKeberatanSheet implements WithEvents, WithTitle
{
    use Exportable;

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $user = auth('admin')->user();

                // Permohonan
                $totalPermohonan = Permohonan::query()
                    ->filterByRole($user)
                    ->count();

                $permohonanBelumDiproses = Permohonan::where('status', 0)
                    ->filterByRole($user)
                    ->count();

                $permohonanDiproses = Permohonan::where('status', 1)
                    ->filterByRole($user)
                    ->count();

                $permohonanDijawab = Permohonan::where('status', 2)
                    ->filterByRole($user)
                    ->count();

                $permohonanDitolak = Permohonan::where('status', 3)
                    ->filterByRole($user)
                    ->count();

                $permohonanSelesai = Permohonan::whereIn('status', [2, 3])
                    ->filterByRole($user)
                    ->count();

                $sheet->setCellValue('A1', 'Permohonan');
                $sheet->mergeCells('A1:C1');
                $this->styleHeader($sheet, "A1:C1");

                $sheet->setCellValue('A2', 'Total Permohonan');
                $sheet->setCellValue('A3', 'Belum Diproses');
                $sheet->setCellValue('A4', 'Sedang Diproses');
                $sheet->setCellValue('A5', 'Selesai');
                $sheet->setCellValue('A6', 'Dijawab');
                $sheet->setCellValue('A7', 'Ditolak');
                $sheet->setCellValue('B2', $totalPermohonan);
                $sheet->setCellValue('B3', $permohonanBelumDiproses);
                $sheet->setCellValue('B4', $permohonanDiproses);
                $sheet->setCellValue('B5', $permohonanSelesai);
                $sheet->setCellValue('B6', $permohonanDijawab);
                $sheet->setCellValue('B7', $permohonanDitolak);
                $sheet->setCellValue('C3', '=B3/B2');
                $sheet->setCellValue('C4', '=B4/B2');
                $sheet->setCellValue('C5', '=B5/B2');
                $sheet->setCellValue('C6', '=B6/B2');
                $sheet->setCellValue('C7', '=B7/B2');

                $sheet->getStyle("A1:C7")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Keberatan
                $totalKeberatan = Keberatan::query()
                    ->filterByRole($user)
                    ->count();

                $keberatanDiterima = Keberatan::where('status', 1)
                    ->filterByRole($user)
                    ->count();

                $keberatanSelesai = Keberatan::where('status', 2)
                    ->filterByRole($user)
                    ->count();

                $sheet->setCellValue('A10', 'Keberatan');
                $sheet->mergeCells('A10:C10');
                $this->styleHeader($sheet, "A10:C10");

                $sheet->setCellValue('A11', 'Total Keberatan');
                $sheet->setCellValue('A12', 'Keberatan Diterima');
                $sheet->setCellValue('A13', 'Keberatan Selesai');
                $sheet->setCellValue('B11', $totalKeberatan);
                $sheet->setCellValue('B12', $keberatanDiterima);
                $sheet->setCellValue('B13', $keberatanSelesai);
                $sheet->setCellValue('C12', '=B12/B11');
                $sheet->setCellValue('C13', '=B13/B11');

                $sheet->getStyle("A10:C13")->applyFromArray([
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
            ->getStartColor()->setARGB('FFFFFF00');
    }

    public function title(): string
    {
        return 'Jumlah Permohonan & Keberatan';
    }
}