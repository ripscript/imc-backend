<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailImageCarouselSeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'image_carousel')
            ->where('is_section', true)
            ->first();

        if (! $content) {
            return;
        }

        $datetime = date('Y-m-d H:i:s');

        for ($i = 1; $i <= 5; $i++) {
            DB::table('content_detail_image_carousels')->insert([
                'content_id' => $content->id,
                'image_path' => "/images/slider-{$i}.jpg",
                'title' => "Slider IMC {$i}",
                'description' => "Deskripsi slider ke-{$i}",
                'link' => '/slider-' . $i,
                'order' => $i,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }
}
