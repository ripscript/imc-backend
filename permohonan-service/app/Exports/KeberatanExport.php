<?php

namespace App\Exports;

use App\Models\Keberatan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class KeberatanExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize
{
    protected $data;

    public function __construct($keberatan)
    {
        $this->data = $keberatan;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Nomor Keberatan',
            'Nomor Permohonan',
            'No. Identitas Pemohon',
            'Nama Pemohon',
            'Kategori Pemohon',
            'Permohonan Informasi',
            'Perihal Keberatan',
            'Status',
            'Tanggal Dimohonkan',
            'Tanggal Diproses',
            'Tanggal Selesai'
        ];
    }

    public function collection()
    {
        $rows = collect();

        $no = 1;
        foreach ($this->data as $index => $item) {
            $tanggapan = $item->tanggapan;
            $tanggalProses = $tanggapan->verified_at ?? null;
            $tanggalSelesai = null;
            if ($item->status == Keberatan::STATUS['DIJAWAB']) {
                $tanggalSelesai = $tanggapan->answered_at ?? null;
            }

            $rows->push([
                $no,
                $item->number,
                $item->permohonan?->number,
                "'" . $item->pemohon?->identity_number ?? '-',
                $item->pemohon?->name ?? '-',
                $item->pemohon?->classification->name ?? '-',
                $item->permohonan?->purpose,
                $item->detail,
                Keberatan::STATUS_TEXT[$item->status] ?? '-',
                $item->created_at->format('d-m-Y'),
                $tanggalProses ? Carbon::parse($tanggalProses)->format('d-m-Y') : '-',
                $tanggalSelesai ? Carbon::parse($tanggalSelesai)->format('d-m-Y') : '-',
            ]);

            $no++;
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:L1')->applyFromArray([
                    'font' => [
                        'bold' => true
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
            }
        ];
    }
}
