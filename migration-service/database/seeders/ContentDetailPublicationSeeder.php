<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailPublicationSeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'publication')
            ->where('is_section', false)
            ->first();

        if (! $content) {
            return;
        }

        $layout = DB::table('ms_layout')->where('type', 'publication')->first();
        $datetime = date('Y-m-d H:i:s');

        $categories = ['Berita', 'Artikel', 'Opini', 'Rilis Pers', 'Liputan Khusus'];
        $slugs = ['berita-1', 'artikel-1', 'opini-1', 'rilis-pers-1', 'liputan-khusus-1'];

        for ($i = 0; $i < 5; $i++) {
            DB::table('content_detail_publications')->insert([
                'content_id' => $content->id,
                'title' => "{$categories[$i]} IMC " . ($i + 1),
                'slug' => $slugs[$i],
                'headline' => "Headline untuk {$categories[$i]} ke-" . ($i + 1),
                'content' => "<p>Isi lengkap {$categories[$i]} ke-" . ($i + 1) . " yang membahas topik relevan mengenai kolaborasi dan informasi publik.</p>",
                'is_publicated' => true,
                'thumbnail_path' => "/images/publikasi-{$i}.jpg",
                'publish_date' => date('Y-m-d', strtotime("-{$i} days")),
                'has_additional_image' => false,
                'has_additional_video' => false,
                'has_additional_file' => false,
                'has_additional_link' => false,
                'has_additional_text' => false,
                'layout_id' => $layout?->id,
                'is_slider' => $i === 0,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }
}
