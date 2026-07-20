<?php

namespace App\Exports;

use App\Models\MsSurveyQuestion;
use App\Models\SurveyKepuasan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ResponseSurveyKepuasanSheet implements WithEvents, WithTitle
{
    use Exportable;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                $pertanyaan =  MsSurveyQuestion::where([
                    'is_active' => true,
                ])->orderBy('order')->get();

                $startDate = $this->request->get('start_date');
                $endDate = $this->request->get('end_date');
                $responses = SurveyKepuasan::with(['permohonan', 'pemohon', 'answers'])
                ->when($startDate, function ($q) use ($startDate) {
                    $q->whereDate('created_at', '>=', $startDate);
                })
                ->when($endDate, function ($q) use ($endDate) {
                    $q->whereDate('created_at', '<=', $endDate);
                })
                ->latest()
                ->get();

                $sheet->setCellValue('A1', 'Survey Kepuasan');
                $sheet->getStyle("A1")->getFont()->setBold(true);

                $rowHeader = 3;
                $sheet->setCellValue('A3', 'No.');
                $sheet->setCellValue('B3', 'Pemohon');
                $sheet->setCellValue('C3', 'No Permohonan');
                $sheet->setCellValue('D3', 'Tanggal Pengisian');
                $sheet->setCellValue('E3', 'Nilai Rata-rata');
                
                $colPertanyaan = [];
                $colHeaderPertanyaan = 'F';
                foreach ($pertanyaan as $value) {
                    $col = $colHeaderPertanyaan."".$rowHeader;
                    $colPertanyaan[$value->id] = $colHeaderPertanyaan;
                    $sheet->setCellValue($col, $value->question);
                    $colHeaderPertanyaan++;
                }
                $this->styleHeader($sheet, "A3:".end($colPertanyaan)."3");

                $rowBody = 4;
                foreach ($responses as $i => $response) {
                    $sheet->setCellValue("A{$rowBody}", ($i+1));
                    $sheet->setCellValue("B{$rowBody}", $response->pemohon?->name);
                    $sheet->setCellValue("C{$rowBody}", $response->permohonan?->number);
                    $sheet->setCellValue("D{$rowBody}", $response->created_at);
                    $sheet->setCellValue("E{$rowBody}", $response->nilai);

                    foreach ($response->answers as $answer) {
                        if (isset($colPertanyaan[$answer->survey_question_id])) {
                            $col = $colPertanyaan[$answer->survey_question_id];
                            $sheet->setCellValue("{$col}{$rowBody}", $answer->readable_answer);
                        }
                    }

                    $rowBody++;
                }
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
            ->getStartColor()->setARGB('FF40E0D0');
    }

    public function title(): string
    {
        return 'Response';
    }
}
