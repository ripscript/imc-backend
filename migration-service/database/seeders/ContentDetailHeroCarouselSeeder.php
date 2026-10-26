<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailHeroCarouselSeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'hero_carousel')
            ->where('is_section', true)
            ->orderBy('id')
            ->first();

        if (! $content) {
            return;
        }

        $datetime = date('Y-m-d H:i:s');

        $slides = [
            ['source_id' => 55, 'order' => 1],
            ['source_id' => 54, 'order' => 2],
            ['source_id' => 51, 'order' => 3],
        ];

        foreach ($slides as $slide) {
            $exists = DB::table('content_detail_hero_carousels')
                ->where('content_id', $content->id)
                ->where('source_type', 'publication')
                ->where('source_id', $slide['source_id'])
                ->first();

            if ($exists) {
                continue;
            }

            DB::table('content_detail_hero_carousels')->insert([
                'content_id' => $content->id,
                'source_type' => 'publication',
                'source_id' => $slide['source_id'],
                'button_text' => 'Selengkapnya',
                'order' => $slide['order'],
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }
}
