<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermohonanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $akses = ["Melihat", "Mendengarkan", "Mencatat", "Membaca"];
        $format = ["Softcopy", "Hardcopy"];
        $salinan = ["Aplikasi", "Langsung", "Email", "Pos (biaya pengiriman ditanggung oleh pemohon informasi)"];

        foreach ($akses as $value) {
            DB::table('ms_permohonan_akses_data')->updateOrInsert([
                'name' => $value
            ]);
        }

        foreach ($format as $value) {
            DB::table('ms_permohonan_format_data')->updateOrInsert([
                'name' => $value
            ]);
        }

        foreach ($salinan as $value) {
            DB::table('ms_permohonan_salinan_informasi')->updateOrInsert([
                'name' => $value
            ]);
        }
        DB::table('ms_permohonan_salinan_informasi')
            ->whereNotIn('name', $salinan)
            ->delete();
    }
}
