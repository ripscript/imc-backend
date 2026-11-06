<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailOrganizationalStructureSeeder extends Seeder
{
    public function run(): void
    {
        $contents = DB::table('contents')
            ->where('type', 'organizational_structure')
            ->get();

        if ($contents->isEmpty()) {
            return;
        }

        $datetime = date('Y-m-d H:i:s');

        foreach ($contents as $content) {
            // Clean up existing groups & members for this content to prevent duplicate seeds
            $existingGroupIds = DB::table('content_detail_organizational_structure_groups')
                ->where('content_id', $content->id)
                ->pluck('id');

            if ($existingGroupIds->isNotEmpty()) {
                DB::table('content_detail_organizational_structure_members')
                    ->whereIn('content_detail_organizational_structure_group_id', $existingGroupIds)
                    ->delete();
                DB::table('content_detail_organizational_structure_groups')
                    ->whereIn('id', $existingGroupIds)
                    ->delete();
            }

            $groupId = DB::table('content_detail_organizational_structure_groups')->insertGetId([
                'content_id' => $content->id,
                'name' => 'Balai Teknologi Industri Manufaktur (BTIM)',
                'order' => 1,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);

            // Level 1: Menteri Perindustrian
            $budiId = DB::table('content_detail_organizational_structure_members')->insertGetId([
                'content_detail_organizational_structure_group_id' => $groupId,
                'name' => 'Budi Santoso',
                'position' => 'MENTERI PERINDUSTRIAN',
                'level' => 1,
                'description' => 'Menteri Perindustrian Republik Indonesia',
                'image_path' => '/images/pejabat/budi-santoso.jpg',
                'is_child' => false,
                'have_child' => true,
                'parent_id' => null,
                'order' => 1,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);

            // Level 2: Dirjen ILMATE
            $adityaId = DB::table('content_detail_organizational_structure_members')->insertGetId([
                'content_detail_organizational_structure_group_id' => $groupId,
                'name' => 'Aditya Wijaya',
                'position' => 'DIRJEN ILMATE',
                'level' => 2,
                'description' => 'Direktur Jenderal Industri Logam, Mesin, Alat Transportasi, dan Elektronika',
                'image_path' => '/images/pejabat/aditya-wijaya.jpg',
                'is_child' => true,
                'have_child' => true,
                'parent_id' => $budiId,
                'order' => 2,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);

            // Level 3: Kepala BTIM
            $farhanId = DB::table('content_detail_organizational_structure_members')->insertGetId([
                'content_detail_organizational_structure_group_id' => $groupId,
                'name' => 'Farhan Maulana',
                'position' => 'KEPALA BALAI TEKNOLOGI INDUSTRI MANUFAKTUR (BTIM)',
                'level' => 3,
                'description' => 'Kepala Balai Teknologi Industri Manufaktur',
                'image_path' => '/images/pejabat/farhan-maulana.jpg',
                'is_child' => true,
                'have_child' => true,
                'parent_id' => $adityaId,
                'order' => 3,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);

            // Level 4 Teams
            $sitiId = DB::table('content_detail_organizational_structure_members')->insertGetId([
                'content_detail_organizational_structure_group_id' => $groupId,
                'name' => 'Siti Nurhaliza',
                'position' => 'TIM INOVASI, PENGEMBANGAN PRODUK DAN TRANSFER TEKNOLOGI',
                'level' => 4,
                'description' => 'Koordinator Tim Inovasi dan Transfer Teknologi',
                'image_path' => '/images/pejabat/siti-nurhaliza.jpg',
                'is_child' => true,
                'have_child' => true,
                'parent_id' => $farhanId,
                'order' => 4,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);

            $anisaId = DB::table('content_detail_organizational_structure_members')->insertGetId([
                'content_detail_organizational_structure_group_id' => $groupId,
                'name' => 'Anisa Rahmawati',
                'position' => 'TIM PENGEMBANGAN TALENTA',
                'level' => 4,
                'description' => 'Koordinator Tim Pengembangan Talenta',
                'image_path' => '/images/pejabat/anisa-rahmawati.jpg',
                'is_child' => true,
                'have_child' => true,
                'parent_id' => $farhanId,
                'order' => 5,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);

            $putriId = DB::table('content_detail_organizational_structure_members')->insertGetId([
                'content_detail_organizational_structure_group_id' => $groupId,
                'name' => 'Putri Lestari',
                'position' => 'TIM PENGEMBANGAN TEKNOLOGI MANUFAKTUR',
                'level' => 4,
                'description' => 'Koordinator Tim Pengembangan Teknologi Manufaktur',
                'image_path' => '/images/pejabat/putri-lestari.jpg',
                'is_child' => true,
                'have_child' => true,
                'parent_id' => $farhanId,
                'order' => 6,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);

            $dewiId = DB::table('content_detail_organizational_structure_members')->insertGetId([
                'content_detail_organizational_structure_group_id' => $groupId,
                'name' => 'Dewi Maharani',
                'position' => 'TIM JEJARING KERJASAMA DAN PEMASARAN',
                'level' => 4,
                'description' => 'Koordinator Tim Jejaring Kerjasama dan Pemasaran',
                'image_path' => '/images/pejabat/dewi-maharani.jpg',
                'is_child' => true,
                'have_child' => true,
                'parent_id' => $farhanId,
                'order' => 7,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);

            // Level 5 Subteams
            $subteams = [
                // Under Siti Nurhaliza
                [
                    'name' => 'Ahmad Dahlan',
                    'position' => 'SUBTIM INOVASI DAN TRANSFER TEKNOLOGI',
                    'parent_id' => $sitiId,
                    'order' => 8,
                ],
                [
                    'name' => 'Amanda Citra',
                    'position' => 'SUBTIM DESAIN DAN PROTOTIPE',
                    'parent_id' => $sitiId,
                    'order' => 9,
                ],
                // Under Anisa Rahmawati
                [
                    'name' => 'Michael Scott',
                    'position' => 'SUBTIM PENGEMBANGAN TALENTA MANUFAKTUR',
                    'parent_id' => $anisaId,
                    'order' => 10,
                ],
                [
                    'name' => 'Daniel Vance',
                    'position' => 'SUBTIM INKUBASI DAN TECHNOPRENEUR',
                    'parent_id' => $anisaId,
                    'order' => 11,
                ],
                // Under Putri Lestari
                [
                    'name' => 'Liam Hudson',
                    'position' => 'SUBTIM TEKNOLOGI PROSES',
                    'parent_id' => $putriId,
                    'order' => 12,
                ],
                [
                    'name' => 'Emily Watson',
                    'position' => 'SUBTIM EFISIENSI DAN TRANSFORMASI DIGITAL',
                    'parent_id' => $putriId,
                    'order' => 13,
                ],
                // Under Dewi Maharani
                [
                    'name' => 'Sophia Bennett',
                    'position' => 'SUBTIM PENGEMBANGAN JEJARING INDUSTRI',
                    'parent_id' => $dewiId,
                    'order' => 14,
                ],
                [
                    'name' => 'Olivia Harris',
                    'position' => 'SUBTIM KERJASAMA DAN PEMASARAN',
                    'parent_id' => $dewiId,
                    'order' => 15,
                ],
            ];

            foreach ($subteams as $st) {
                DB::table('content_detail_organizational_structure_members')->insert([
                    'content_detail_organizational_structure_group_id' => $groupId,
                    'name' => $st['name'],
                    'position' => $st['position'],
                    'level' => 5,
                    'description' => "Personil {$st['position']}",
                    'image_path' => '/images/pejabat/default.jpg',
                    'is_child' => true,
                    'have_child' => false,
                    'parent_id' => $st['parent_id'],
                    'order' => $st['order'],
                    'created_by' => 'seeder',
                    'updated_by' => 'seeder',
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
