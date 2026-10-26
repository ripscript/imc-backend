<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LatestInformationSeeder extends Seeder
{
    public function run(): void
    {
        $datetime = date('Y-m-d H:i:s');

        $items = [
            [
                'title' => 'Kementerian Perindustrian Perkuat Identifikasi Kebutuhan dan Perkembangan Pasar Alsintan',
                'url' => '/berita-dan-artikel/kementerian-perindustrian-perkuat-identifikasi-kebutuhan-dan-perkembangan-pasar-alsintan',
                'publish_date' =>  '2026-05-06',
                'is_active' => true,
            ],
            [
                'title' => 'EV Mining Outlook : Elektrifikasi Pertambangan Bisa Sukses, Kemenperin Ungkap Syaratnya',
                'url' => '/berita-dan-artikel/ev-mining-outlook-elektrifikasi-pertambangan-bisa-sukses-kemenperin-ungkap-syaratnya  ',
                'publish_date' => '2026-05-05',
                'is_active' => true,
            ],
            [
                'title' => 'FGD Strategi Akselerasi Adopsi Robotika: IMC Dorong Kemandirian Permesinan Nasional',
                'url' => '/berita-dan-artikel/fgd-strategi-akselerasi-adopsi-robotika-imc-dorong-kemandirian-permesinan-nasional',
                'publish_date' => '2026-05-06',
                'is_active' => true,
            ],
            [
                'title' => 'Tugas dan Fungsi Balai Teknologi Industri Manufaktur (BTIM) / IMC',
                'url' => '/berita-dan-artikel/tugas-dan-fungsi-balai-teknologi-industri-manufaktur-btim-imc',
                'publish_date' => '2026-01-18',
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            $existing = DB::table('latest_informations')->where('url', $item['url'])->first();

            if (!$existing) {
                DB::table('latest_informations')->insert(array_merge($item, [
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]));
            }
        }
    }
}
