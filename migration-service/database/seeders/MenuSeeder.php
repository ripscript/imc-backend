<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datetime = date('Y-m-d H:i:s');

        // Beranda
        $findHome = DB::table('menus')->where("slug", 'beranda')->first();
        if (!$findHome) {
            DB::table('menus')->insert(
                [
                    'title' => 'Beranda',
                    'slug' => 'beranda',
                    'is_active' => 1,
                    'is_dedicated' => 1,
                    'has_sub' => false,
                    'order' => 1,
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                    'is_show_front' => true
                ]
            );
        }

        // Section Beranda
        $getHome = DB::table('menus')->where("slug", 'beranda')->first();
        $dataSection = [
            ['title' => 'Slider', 'type' => 'image_carousel'],
            ['title' => 'Video Dokumentasi', 'type' => 'video_carousel'],
            ['title' => 'Dokumen Publik', 'type' => 'consume'],
            ['title' => 'Berita Terkini', 'type' => 'consume'],
            ['title' => 'Layanan Lainnya', 'type' => 'image_link'],
        ];
        foreach ($dataSection as $key => $value) {
            $findSection = DB::table('contents')->where([
                'parent_type' => 'App\Models\Menu',
                'parent_id' => $getHome->id,
                'section_title' => $value['title']
            ])->first();
            if (!$findSection) {
                DB::table('contents')->insert(
                    [
                        'parent_type' => 'App\Models\Menu',
                        'parent_id' => $getHome->id,
                        'section_title' => $value['title'],
                        'type' => $value['type'],
                        'order' => $key+1,
                        'is_section' => true,
                        'created_at' => $datetime,
                        'updated_at' => $datetime,
                    ]
                );
            }
        }

        // Berita
        $findBerita = DB::table('menus')->where('slug', 'berita')->first();
        if (!$findBerita) {
            $menuBeritaId = DB::table('menus')->insertGetId([
                'title' => 'Berita',
                'slug' => 'berita',
                'is_active' => 1,
                'is_dedicated' => 1,
                'has_sub' => false,
                'order' => 2,
                'created_at' => $datetime,
                'updated_at' => $datetime,
                'is_show_front' => true
            ]);

            $layout = DB::table('ms_layout')->where('type', "publication")->first();
            $contentId = DB::table('contents')->insertGetId([
                'is_show_home' => true,
                'is_accessible' => true,
                'is_show_tag' => true,
                'type' => 'publication',
                'parent_type' => 'App\Models\Menu',
                'parent_id' => $menuBeritaId,
                'layout_id' => $layout->id,
                'created_at' => $datetime,
                'updated_at' => $datetime
            ]);
        }

        // Layanan lainnya
        $findLayananLainnya = DB::table('menus')->where('slug', 'layanan-lainnya')->first();
        if (!$findLayananLainnya) {
            $menuLayananLainnyaId = DB::table('menus')->insertGetId([
                'title' => 'Layanan Lainnya',
                'slug' => 'layanan-lainnya',
                'is_active' => 1,
                'is_dedicated' => 0,
                'has_sub' => false,
                'order' => 3,
                'created_at' => $datetime,
                'updated_at' => $datetime,
                'is_show_front' => false
            ]);

            $layout = DB::table('ms_layout')->where('type', "image_link")->first();
            $contentId = DB::table('contents')->insertGetId([
                'is_show_home' => false,
                'is_accessible' => true,
                'is_show_tag' => false,
                'type' => 'image_link',
                'parent_type' => 'App\Models\Menu',
                'parent_id' => $menuLayananLainnyaId,
                'layout_id' => $layout->id,
                'created_at' => $datetime,
                'updated_at' => $datetime
            ]);
        }
    }
}
