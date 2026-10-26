<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailOrganizationalStructureSeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'organizational_structure')
            ->where('is_section', true)
            ->first();

        if (! $content) {
            return;
        }

        $datetime = date('Y-m-d H:i:s');

        for ($i = 1; $i <= 5; $i++) {
            $groupId = DB::table('content_detail_organizational_structure_groups')->insertGetId([
                'content_id' => $content->id,
                'name' => "Unit Organisasi {$i}",
                'order' => $i,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);

            for ($j = 1; $j <= 3; $j++) {
                $hasChild = $j === 1;
                $parentId = null;

                if ($j > 1) {
                    $parentId = DB::table('content_detail_organizational_structure_members')
                        ->where('content_detail_organizational_structure_group_id', $groupId)
                        ->where('is_child', false)
                        ->where('have_child', true)
                        ->value('id');
                }

                DB::table('content_detail_organizational_structure_members')->insert([
                    'content_detail_organizational_structure_group_id' => $groupId,
                    'name' => "Anggota {$i}.{$j}",
                    'level' => $j,
                    'position' => $j === 1 ? 'Kepala Unit' : 'Staf',
                    'description' => "Deskripsi anggota ke-{$j} dari unit {$i}.",
                    'image_path' => "/images/anggota-{$i}-{$j}.jpg",
                    'is_child' => $j > 1,
                    'have_child' => $hasChild,
                    'parent_id' => $parentId,
                    'order' => $j,
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
