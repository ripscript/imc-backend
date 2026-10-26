<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailCardLinkSeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'card_link')
            ->where('is_section', true)
            ->first();

        if (!$content) {
            return;
        }

        $datetime = date('Y-m-d H:i:s');

        $cards = [
            [
                'title' => 'Konsultasi Industri',
                'color_code' => '#F59E0B',
                'link' => '/layanan/konsultasi-industri',
                'order' => 1,
            ],
            [
                'title' => 'Sertifikasi Produk',
                'color_code' => '#10B981',
                'link' => '/layanan/sertifikasi-produk',
                'order' => 2,
            ],
            [
                'title' => 'Pelatihan SDM',
                'color_code' => '#3B82F6',
                'link' => '/layanan/pelatihan-sdm',
                'order' => 3,
            ],
            [
                'title' => 'Riset & Pengembangan',
                'color_code' => '#8B5CF6',
                'link' => '/layanan/riset-pengembangan',
                'order' => 4,
            ],
        ];

        foreach ($cards as $card) {
            $existing = DB::table('content_detail_card_links')
                ->where('content_id', $content->id)
                ->where('title', $card['title'])
                ->first();

            if (!$existing) {
                DB::table('content_detail_card_links')->insert([
                    'content_id' => $content->id,
                    'title' => $card['title'],
                    'color_code' => $card['color_code'],
                    'link' => $card['link'],
                    'order' => $card['order'],
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
