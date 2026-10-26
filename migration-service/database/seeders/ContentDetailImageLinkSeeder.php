<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailImageLinkSeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'image_link')
            ->where('is_section', true)
            ->first();

        if (!$content) {
            return;
        }

        $datetime = date('Y-m-d H:i:s');

        $ecosystemSteps = [
            ['title' => 'Ide & Kebutuhan Industri', 'image_path' => '/images/ekosistem-ide.jpg', 'link' => '/ekosistem/ide', 'order' => 1],
            ['title' => 'Desain & Rekayasa', 'image_path' => '/images/ekosistem-desain.jpg', 'link' => '/ekosistem/desain', 'order' => 2],
            ['title' => 'Prototipe', 'image_path' => '/images/ekosistem-prototipe.jpg', 'link' => '/ekosistem/prototipe', 'order' => 3],
            ['title' => 'Pengujian & Validasi', 'image_path' => '/images/ekosistem-pengujian.jpg', 'link' => '/ekosistem/pengujian', 'order' => 4],
            ['title' => 'Pilot Production', 'image_path' => '/images/ekosistem-pilot.jpg', 'link' => '/ekosistem/pilot', 'order' => 5],
            ['title' => 'Komersialisasi Produk', 'image_path' => '/images/ekosistem-komersialisasi.jpg', 'link' => '/ekosistem/komersialisasi', 'order' => 6],
        ];

        foreach ($ecosystemSteps as $step) {
            $existing = DB::table('content_detail_image_links')
                ->where('content_id', $content->id)
                ->where('title', $step['title'])
                ->first();

            if (!$existing) {
                DB::table('content_detail_image_links')->insert([
                    'content_id' => $content->id,
                    'image_path' => $step['image_path'],
                    'title' => $step['title'],
                    'link' => $step['link'],
                    'order' => $step['order'],
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
