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

        if (! $content) {
            return;
        }

        $datetime = date('Y-m-d H:i:s');
        $colors = ['#FF5733', '#33FF57', '#3357FF', '#F033FF', '#FF33A1'];
        $titles = ['Pengumuman', 'Layanan Publik', 'Dokumen Resmi', 'Infografis', 'Edukasi'];

        for ($i = 0; $i < 5; $i++) {
            DB::table('content_detail_card_links')->insert([
                'content_id' => $content->id,
                'title' => $titles[$i],
                'color_code' => $colors[$i],
                'link' => '/link-' . ($i + 1),
                'order' => $i + 1,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }
}
