<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'A' => 'text',
            'B' => 'card_link',
            'C' => 'image_link',
            'D' => 'image_carousel',
            'E' => 'video_carousel',
            'F' => 'publication',
            'G' => 'publication_detail',
            'H' => 'gallery',
            'I' => 'gallery_image_detail',
            'J' => 'gallery_video_detail',
            'K' => 'repository',
            'L' => 'repository_detail',
            'M' => 'accordion',
            'N' => 'landing_page',
            'O' => 'agenda',
            'P' => 'organizational_structure'
        ];

        $custom = [];
        foreach ($data as $key => $value) {
            $title = "Style " . $key;
            $isCustom = in_array($key, $custom) ? true : false;

            $existingRecord = DB::table('ms_layout')->where('title', $title)->first();
            if ($existingRecord) {
                DB::table('ms_layout')->where('title', $title)->update(['type' => $value, 'is_custom' => $isCustom]);
            } else {
                DB::table('ms_layout')->insert(['title' => $title, 'type' => $value, 'is_custom' => $isCustom]);
            }
        }
    }
}
