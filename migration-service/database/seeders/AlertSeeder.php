<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'code' => 'permohonan_diproses',
                'title' => 'Permohonan Sedang Diproses',
                'message' => 'Permohonan Anda akan diproses dalam jangka waktu maksimal 10 (sepuluh) hari kerja. Saat ini petugas sedang melakukan pemeriksaan dokumen pendukung dan verifikasi informasi sesuai ketentuan yang berlaku. Anda akan menerima pemberitahuan setelah proses selesai.',
                'type' => 'warning',
            ],
            [
                'code' => 'permohonan_need_additional_time',
                'title' => 'Permohonan Memerlukan Waktu Tambahan',
                'message' => 'Proses penyediaan informasi memerlukan perpanjangan waktu selama maksimal 7 (tujuh) hari kerja. Hal ini disebabkan masih diperlukannya penelaahan secara menyeluruh serta verifikasi data lanjutan. Informasi lebih lanjut akan disampaikan pada kesempatan berikutnya. Terima kasih atas perhatian dan kesabaran Anda.',
                'type' => 'warning',
            ]
        ];

        foreach ($items as $item) {
            DB::table('ms_alerts')->updateOrInsert(
                ['code' => $item['code']],
                array_merge($item, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $codes = array_column($items, 'code');
        DB::table('ms_alerts')->whereNotIn('code', $codes)->delete();
    }
}
