<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailVideoCarouselSeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'video_carousel')
            ->where('is_section', true)
            ->first();

        if (! $content) {
            return;
        }

        $datetime = date('Y-m-d H:i:s');

        for ($i = 1; $i <= 5; $i++) {
            DB::table('content_detail_video_carousels')->insert([
                'content_id' => $content->id,
                'title' => "Video Dokumentasi {$i}",
                'type' => 'link',
                'url' => "https://youtube.com/watch?v=video{$i}",
                'video_path' => null,
                'order' => $i,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }
}
