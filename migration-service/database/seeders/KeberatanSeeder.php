<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KeberatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $alasan = [
            "Penolakan atas permintaan informasi berdasarkan alasan pengecualian",
            "Tidak disediakannya informasi berkala",
            "Tidak ditanggapinya permintaan informasi",
            "Permintaan informasi ditanggapi tidak sebagaimana yang diminta",
            "Tidak dipenuhinya permintaan informasi",
            "Pengenaan biaya yang tidak wajar",
            "Penyampaian informasi yang melebihi waktu yang diatur dalam Undang-Undang"
        ];

        foreach ($alasan as $value) {
            DB::table('ms_alasan_keberatan')->updateOrInsert([
                'name' => $value
            ]);
        }
    }
}
