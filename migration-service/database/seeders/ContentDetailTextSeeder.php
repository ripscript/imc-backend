<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailTextSeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'text')
            ->where('is_section', false)
            ->first();

        if (! $content) {
            return;
        }

        $datetime = date('Y-m-d H:i:s');

        for ($i = 1; $i <= 5; $i++) {
            DB::table('content_detail_texts')->insert([
                'content_id' => $content->id,
                'content' => "<p>Tentang IMC adalah konten teks ke-{$i} yang menjelaskan informasi mengenai Indeks Manusia dan Kolaborasi.</p>",
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }
}
