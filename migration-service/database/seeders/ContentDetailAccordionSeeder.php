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

        if (! $content) {
            return;
        }

        $datetime = date('Y-m-d H:i:s');

        for ($i = 1; $i <= 5; $i++) {
            $groupId = DB::table('content_detail_accordion_groups')->insertGetId([
                'content_id' => $content->id,
                'title' => "Kelompok FAQ {$i}",
                'order' => $i,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);

            for ($j = 1; $j <= 3; $j++) {
                DB::table('content_detail_accordion_lists')->insert([
                    'content_detail_accordion_group_id' => $groupId,
                    'title' => "Pertanyaan {$i}.{$j}",
                    'description' => "Ini adalah jawaban untuk pertanyaan kelompok {$i} nomor {$j}.",
                    'file_path' => null,
                    'link' => null,
                    'order' => $j,
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
