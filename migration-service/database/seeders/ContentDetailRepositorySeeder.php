<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailRepositorySeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'repository')
            ->where('is_section', true)
            ->first();

        if (! $content) {
            return;
        }

        $layout = DB::table('ms_layout')->where('type', 'repository')->first();
        $datetime = date('Y-m-d H:i:s');

        for ($i = 1; $i <= 5; $i++) {
            DB::table('content_detail_repositories')->insert([
                'content_id' => $content->id,
                'title' => "Dokumen Repository {$i}",
                'slug' => "dokumen-repository-{$i}",
                'description' => "Deskripsi dokumen repository ke-{$i} yang berisi informasi penting.",
                'file_path' => "/files/dokumen-{$i}.pdf",
                'thumbnail_path' => "/images/thumbnail-repo-{$i}.jpg",
                'is_publicated' => true,
                'publish_date' => date('Y-m-d', strtotime("-{$i} days")),
                'layout_id' => $layout?->id,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }
}
