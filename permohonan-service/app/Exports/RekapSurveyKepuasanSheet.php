<?php

namespace App\Exports;

use App\Models\MsSurveyQuestion;
use App\Models\SurveyKepuasan;
use App\Models\SurveyKepuasanAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RekapSurveyKepuasanSheet implements WithEvents, WithTitle
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

                $sheet->setCellValue('A1', 'Rekap Survey Kepuasan');
                $sheet->getStyle("A1")->getFont()->setBold(true);

                $startDate = $this->request->get('start_date');
                $endDate = $this->request->get('end_date');

                $totalResponse = SurveyKepuasan::when($startDate, function ($q) use ($startDate) {
                    $q->whereDate('created_at', '>=', $startDate);
                })
                ->when($endDate, function ($q) use ($endDate) {
                    $q->whereDate('created_at', '<=', $endDate);
                })
                ->count();

                $avgTotalNilai = SurveyKepuasan::when($startDate, function ($q) use ($startDate) {
                    $q->whereDate('created_at', '>=', $startDate);
                })
                ->when($endDate, function ($q) use ($endDate) {
                    $q->whereDate('created_at', '<=', $endDate);
                })
                ->average('nilai');

                $pertanyaanSkalaLikert = MsSurveyQuestion::where([
                    'is_active' => true,
                    'type' => 'skala_likert'
                ])->orderBy('order')->get();
                
                $skorPerPertanyaan = SurveyKepuasanAnswer::when($startDate, function ($q) use ($startDate) {
                    $q->whereDate('created_at', '>=', $startDate);
                })
                ->when($endDate, function ($q) use ($endDate) {
                    $q->whereDate('created_at', '<=', $endDate);
                })
                ->select(
                    'survey_question_id',
                    DB::raw('AVG(bobot) as avg_bobot')
                )
                ->groupBy('survey_question_id')
                ->pluck('avg_bobot', 'survey_question_id')
                ->toArray();

                $pertanyaan = [];
                foreach ($pertanyaanSkalaLikert as $value) {
                    $pertanyaan[] = [
                        'pertanyaan' => $value->question,
                        'rata_rata_nilai' => round($skorPerPertanyaan[$value->id], 2) ?? 0
                    ];
                }

                $sheet->setCellValue('A3', 'Total Response');
                $sheet->getStyle("A3")->getFont()->setBold(true);
                $sheet->setCellValue('B3', $totalResponse);
                $sheet->setCellValue('A4', 'Nilai Rata-rata');
                $sheet->getStyle("A4")->getFont()->setBold(true);
                $sheet->setCellValue('B4', round($avgTotalNilai, 2));

                $sheet->setCellValue('A6', 'No.');
                $sheet->setCellValue('B6', 'Pertanyaan');
                $sheet->setCellValue('C6', 'Rata-rata nilai');
                $this->styleHeader($sheet, "A6:C6");

                $row = 7;
                foreach ($pertanyaan as $i => $value) {
                    $sheet->setCellValue("A{$row}", ($i+1));
                    $sheet->setCellValue("B{$row}", $value['pertanyaan']);
                    $sheet->setCellValue("C{$row}", $value['rata_rata_nilai']);

                    $row++;
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
            ->getStartColor()->setARGB('FFEE82EE');
    }

    public function title(): string
    {
        return 'Rekap';
    }
}
