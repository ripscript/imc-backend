<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class HolidayImport implements SkipsEmptyRows, ToCollection
{
    public $invalidDates = [];

    public $parsedRows = [];

    public function collection(Collection $rows)
    {
        $user = auth('admin')->user()->name;
        $now = now();
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }

            if (empty(array_filter($row->toArray()))) {
                continue;
            }

            $rowNumber = $index + 2;

            $raw = trim($row[1] ?? '');

            if ($raw === '') {
                continue;
            }

            $parsed = $this->parseExcelDate($raw);

            if (! $parsed) {
                $this->invalidDates[] = [
                    'row' => $rowNumber,
                    'value' => $raw,
                ];

                continue;
            }

            $this->parsedRows[] = [
                'date' => $parsed,
                'name' => $row[2] ?? null,
                'created_by' => $user,
                'updated_by' => $user,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }

    private function parseExcelDate($value)
    {
        try {
            // Jika date numeric -> excel serial
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject($value))->format('Y-m-d');
            }

            // Jika format teks
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
