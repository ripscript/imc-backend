<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailConsumeSeeder extends Seeder
{
    public function run(): void
    {
        $datetime = date('Y-m-d H:i:s');

        // Consume sections under Beranda
        $consumeSections = DB::table('contents')
            ->where('type', 'consume')
            ->where('is_section', true)
            ->get();

        // Menu content records for consume targets
        $proyekContent = DB::table('contents')
            ->where('type', 'project')
            ->where('is_section', false)
            ->first();

        $showcaseContent = DB::table('contents')
            ->where('type', 'showcase')
            ->where('is_section', false)
            ->first();

        foreach ($consumeSections as $section) {
            $consumeType = match ($section->section_title) {
                'Proyek Kolaborasi' => 'project',
                'Showcase' => 'showcase',
                default => 'publication',
            };

            $targetContent = match ($consumeType) {
                'project' => $proyekContent,
                'showcase' => $showcaseContent,
                default => null,
            };

            if (! $targetContent) {
                continue;
            }

            for ($i = 1; $i <= 5; $i++) {
                DB::table('content_detail_consumes')->insert([
                    'content_id' => $section->id,
                    'consume_type' => $consumeType,
                    'consume_id' => $targetContent->id,
                    'order' => $i,
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
