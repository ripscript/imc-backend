<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class BulkPasswordTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new TemplateUpdateSheet,
            new DataUserSheet,
        ];
    }
}

class TemplateUpdateSheet implements FromCollection, WithEvents, WithHeadings, WithTitle
{
    public function collection()
    {
        return collect([]);
    }

    public function headings(): array
    {
        return ['No', 'Email', 'New Password'];
    }

    public function title(): string
    {
        return 'Template Update';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:C1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => 'FEC288'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ]);

                foreach (['A' => 6, 'B' => 30, 'C' => 25] as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }
            },
        ];
    }
}

class DataUserSheet implements FromCollection, WithEvents, WithHeadings, WithTitle
{
    public function collection()
    {
        $users = User::select(
            'users.email',
            'users.username',
            'users.name',
            'ms_roles.name as role_name',
            'ms_satuan_kerja.name as satuan_kerja_name',
            'ms_perwakilan.name as perwakilan_name'
        )
            ->join('ms_roles', 'users.role_id', '=', 'ms_roles.id')
            ->leftJoin('ms_satuan_kerja', 'users.satuan_kerja_id', '=', 'ms_satuan_kerja.id')
            ->leftJoin('ms_perwakilan', 'users.perwakilan_id', '=', 'ms_perwakilan.id')
            ->get();

        $data = new Collection;
        $no = 1;
        foreach ($users as $user) {
            $data->push([
                'no' => $no++,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role_name,
                'institution' => $user->satuan_kerja_name ?? $user->perwakilan_name ?? '-',
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Email',
            'Username',
            'Role',
            'Satker/Perwakilan',
        ];
    }

    public function title(): string
    {
        return 'Data User';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:F1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => 'AAFBB6'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ]);

                $widths = [
                    'A' => 6,
                    'B' => 25,
                    'C' => 30,
                    'D' => 20,
                    'E' => 20,
                    'F' => 25,
                ];

                foreach ($widths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal('center');
            },
        ];
    }
}
