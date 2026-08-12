<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MsTagSeeder extends Seeder
{
    public function run(): void
    {
        $datetime = date('Y-m-d H:i:s');

        $tags = [
            ['title' => 'Teknologi', 'is_active' => true],
            ['title' => 'Manufaktur', 'is_active' => true],
            ['title' => 'Kolaborasi', 'is_active' => true],
            ['title' => 'Inovasi', 'is_active' => true],
            ['title' => 'Industri 4.0', 'is_active' => true],
            ['title' => 'Mesin', 'is_active' => true],
            ['title' => 'Drone', 'is_active' => true],
            ['title' => 'Kesehatan', 'is_active' => true],
            ['title' => 'Energi', 'is_active' => true],
            ['title' => 'Pertahanan', 'is_active' => true],
        ];

        foreach ($tags as $tag) {
            $existing = DB::table('ms_tags')->where('title', $tag['title'])->first();

            if (!$existing) {
                DB::table('ms_tags')->insert(array_merge($tag, [
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]));
            }
        }
    }
}
