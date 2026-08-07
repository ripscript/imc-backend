<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailGallerySeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'gallery')
            ->where('is_section', false)
            ->first();

        if (! $content) {
            return;
        }

        $layout = DB::table('ms_layout')->where('type', 'gallery')->first();
        $datetime = date('Y-m-d H:i:s');

        for ($i = 1; $i <= 5; $i++) {
            $galleryId = DB::table('content_detail_galleries')->insertGetId([
                'content_id' => $content->id,
                'title' => "Galeri Showcase {$i}",
                'slug' => "galeri-showcase-{$i}",
                'description' => "Deskripsi galeri showcase ke-{$i}.",
                'is_publicated' => true,
                'publish_date' => date('Y-m-d', strtotime("-{$i} days")),
                'layout_id' => $layout?->id,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);

            for ($j = 1; $j <= 3; $j++) {
                DB::table('content_detail_gallery_lists')->insert([
                    'content_detail_gallery_id' => $galleryId,
                    'description' => "Deskripsi item galeri {$i}.{$j}",
                    'image_path' => "/images/galeri-{$i}-{$j}.jpg",
                    'video_type' => null,
                    'video_path' => null,
                    'video_url' => null,
                    'order' => $j,
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
