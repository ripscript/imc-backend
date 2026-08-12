<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentTagSeeder extends Seeder
{
    public function run(): void
    {
        $datetime = date('Y-m-d H:i:s');

        $publications = DB::table('content_detail_publications')
            ->orderBy('id', 'asc')
            ->limit(5)
            ->get();

        $tags = DB::table('ms_tags')
            ->orderBy('id', 'asc')
            ->get();

        if ($publications->isEmpty() || $tags->isEmpty()) {
            return;
        }

        $tagMap = [
            1 => [1, 4],
            2 => [2, 5],
            3 => [3, 6],
            4 => [8, 9],
            5 => [7, 10],
        ];

        foreach ($publications as $publication) {
            $tagIds = $tagMap[$publication->id] ?? [];

            foreach ($tagIds as $tagId) {
                $tag = $tags->firstWhere('id', $tagId);
                if (!$tag) {
                    continue;
                }

                DB::table('content_tags')->insert([
                    'content_type' => 'App\Models\ContentDetailPublication',
                    'content_id' => $publication->id,
                    'tag_id' => $tag->id,
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
