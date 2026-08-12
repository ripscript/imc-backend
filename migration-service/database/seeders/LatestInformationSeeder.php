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
                'title' => 'EV Mining Outlook: Elektrifikasi Pertambangan Bisa Sukses, Kemenperin Ungkap Syaratnya',
                'url' => '/berita-dan-artikel/ev-mining-outlook-elektrifikasi-pertambangan',
                'publish_date' => date('Y-m-d', strtotime('-3 days')),
                'is_active' => true,
            ],
            [
                'title' => '7 Ways You Can Reduce Climate Change',
                'url' => '/berita-dan-artikel/7-ways-reduce-climate-change',
                'publish_date' => date('Y-m-d', strtotime('-7 days')),
                'is_active' => true,
            ],
            [
                'title' => '7 Ways You Can Reduce Climate Change',
                'url' => '/berita-dan-artikel/7-ways-reduce-climate-change-2',
                'publish_date' => date('Y-m-d', strtotime('-14 days')),
                'is_active' => true,
            ],
            [
                'title' => '7 Ways You Can Reduce Climate Change',
                'url' => '/berita-dan-artikel/7-ways-reduce-climate-change-3',
                'publish_date' => date('Y-m-d', strtotime('-21 days')),
                'is_active' => true,
            ],
            [
                'title' => '7 Ways You Can Reduce Climate Change',
                'url' => '/berita-dan-artikel/7-ways-reduce-climate-change-4',
                'publish_date' => date('Y-m-d', strtotime('-28 days')),
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
