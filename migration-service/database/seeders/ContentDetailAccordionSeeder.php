<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailAccordionSeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'accordion')
            ->where('is_section', true)
            ->first();

        if (!$content) {
            return;
        }

        $datetime = date('Y-m-d H:i:s');

        // Tujuan Strategis IMC
        $groupId1 = DB::table('content_detail_accordion_groups')->insertGetId([
            'content_id' => $content->id,
            'title' => 'Tujuan Strategis IMC',
            'order' => 1,
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ]);

        $strategicObjectives = [
            ['title' => 'One-Stop Services', 'description' => 'Menjadi One-Stop Services bagi pengembangan teknologi dan inovasi industri', 'order' => 1],
            ['title' => 'Produk Substitusi Impor', 'description' => 'Mendorong produk substitusi impor dalam sektor manufaktur nasional', 'order' => 2],
            ['title' => 'Daya Saing Industri', 'description' => 'Meningkatkan kinerja dan daya saing industri manufaktur', 'order' => 3],
            ['title' => 'Technopreneur Baru', 'description' => 'Menciptakan technopreneur dan pelaku industri baru', 'order' => 4],
            ['title' => 'Rantai Pasok Nasional', 'description' => 'Memperkuat rantai pasok nasional berbasis teknologi', 'order' => 5],
        ];

        foreach ($strategicObjectives as $obj) {
            $existing = DB::table('content_detail_accordion_lists')
                ->where('content_detail_accordion_group_id', $groupId1)
                ->where('title', $obj['title'])
                ->first();

            if (!$existing) {
                DB::table('content_detail_accordion_lists')->insert([
                    'content_detail_accordion_group_id' => $groupId1,
                    'title' => $obj['title'],
                    'description' => $obj['description'],
                    'file_path' => null,
                    'link' => null,
                    'order' => $obj['order'],
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }

        // Sektor Prioritas IMC
        $groupId2 = DB::table('content_detail_accordion_groups')->insertGetId([
            'content_id' => $content->id,
            'title' => 'Sektor Prioritas IMC',
            'order' => 2,
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ]);

        $prioritySectors = [
            ['title' => 'Alat Berat dan Konstruksi', 'description' => 'Industri alat berat dan konstruksi untuk mendukung proyek infrastruktur nasional', 'order' => 1],
            ['title' => 'Peralatan Pembangkit Listrik dan Penunjang Migas', 'description' => 'Pengembangan peralatan pembangkit listrik dan pendukung industri minyak dan gas', 'order' => 2],
            ['title' => 'Pertanian, Kehutanan, Perikanan dan Kelautan', 'description' => 'Teknologi mesin untuk sektor pertanian, kehutanan, perikanan, dan kelautan', 'order' => 3],
            ['title' => 'Permesinan Perkakas', 'description' => 'Pengembangan mesin perkakas industri manufaktur', 'order' => 4],
            ['title' => 'Pertahanan dan Keamanan', 'description' => 'Teknologi untuk mendukung sektor pertahanan dan keamanan nasional', 'order' => 5],
            ['title' => 'Tekstil, Garmen dan Alas Kaki', 'description' => 'Inovasi mesin untuk industri tekstil, garmen, dan alas kaki', 'order' => 6],
            ['title' => 'Elektronik dan Komunikasi', 'description' => 'Pengembangan peralatan elektronik dan sistem komunikasi', 'order' => 7],
            ['title' => 'Alat Kesehatan dan Farmasi', 'description' => 'Produksi alat kesehatan dan farmasi untuk mendukung layanan kesehatan nasional', 'order' => 8],
        ];

        foreach ($prioritySectors as $sector) {
            $existing = DB::table('content_detail_accordion_lists')
                ->where('content_detail_accordion_group_id', $groupId2)
                ->where('title', $sector['title'])
                ->first();

            if (!$existing) {
                DB::table('content_detail_accordion_lists')->insert([
                    'content_detail_accordion_group_id' => $groupId2,
                    'title' => $sector['title'],
                    'description' => $sector['description'],
                    'file_path' => null,
                    'link' => null,
                    'order' => $sector['order'],
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
