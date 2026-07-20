<?php

namespace App\Exports;

use App\Helpers\DateHelper;
use Carbon\Carbon;
use App\Models\Permohonan;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithEvents,
    ShouldAutoSize
};
use Maatwebsite\Excel\Events\AfterSheet;

class PermohonanExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize
{
    protected $data;

    public function __construct($permohonan)
    {
        $this->data = $permohonan;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Nomor Permohonan',
            'No. Identitas Pemohon',
            'Nama Pemohon',
            'Kategori Pemohon',
            'Permohonan',
            'Tanggal Dibuat',

            // repeating section
            'Instansi Tujuan',
            'Status',
            'Tanggal Diproses',
            'Waktu Proses',
            'Tanggal Selesai'
        ];
    }

    public function collection()
    {
        $rows = collect();

        $no = 1;
        foreach ($this->data as $index => $item) {
            $globalTanggalSelesai = null;
            if ($item->status == Permohonan::STATUS['DITOLAK']) {
                $firstResponse = $item->tanggapan()->orderBy('id')->first();
                $globalTanggalSelesai = $firstResponse->rejected_at ?? null;
            }
            
            $institutions = $item->institutions;

            if (count($institutions) > 0) {
                foreach ($institutions as $inst) {
                    $tanggapan = $inst->tanggapan;
    
                    $tanggalProses = $tanggapan->verified_at ?? null;

                    $tanggalSelesai = null;
                    if ($item->status == Permohonan::STATUS['DITOLAK']) {
                        $tanggalSelesai = $globalTanggalSelesai;
                    } else if ($item->status == Permohonan::STATUS['DIJAWAB']) {
                        $tanggalSelesai = $tanggapan->answered_at ?? null;
                    }
    
                    $waktuProses = '-';
                    if ($tanggalProses && $tanggalSelesai) {
                        $waktuProses = DateHelper::remainingDays($tanggalSelesai, true, $tanggalProses) . ' hari';
                    }
    
                    $rows->push([
                        $no,
                        $item->number,
                        "'" . $item->pemohon?->identity_number ?? '-',
                        $item->pemohon?->name ?? '-',
                        $item->pemohon?->classification->name ?? '-',
                        $item->purpose,
                        $item->created_at->format('d-m-Y'),
                        $inst->name ?? '-',
                        Permohonan::STATUS_TEXT[$inst->status] ?? '-',
                        $tanggalProses ? Carbon::parse($tanggalProses)->format('d-m-Y') : '-',
                        $waktuProses,
                        $tanggalSelesai ? Carbon::parse($tanggalSelesai)->format('d-m-Y') : '-',
                    ]);

                    $no++;
                }
            } else {
                $rows->push([
                    $no,
                    $item->number,
                    "'" . $item->pemohon?->identity_number ?? '-',
                    $item->pemohon?->name ?? '-',
                    $item->pemohon?->classification->name ?? '-',
                    $item->purpose,
                    $item->created_at->format('d-m-Y'),
                    '-',
                    Permohonan::STATUS_TEXT[$item->status] ?? '-',
                    '-',
                    '-',
                    '-',
                ]);

                $no++;
            }
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
