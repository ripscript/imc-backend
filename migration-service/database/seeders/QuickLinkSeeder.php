<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuickLinkSeeder extends Seeder
{
    public function run(): void
    {
        $datetime = date('Y-m-d H:i:s');

        $links = [
            [
                'title' => 'Pengumuman',
                'url' => '/pengumuman',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'title' => 'Layanan Publik',
                'url' => '/layanan-publik',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'title' => 'Dokumen Resmi',
                'url' => '/dokumen-resmi',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'title' => 'Infografis',
                'url' => '/infografis',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'title' => 'Edukasi',
                'url' => '/edukasi',
                'is_active' => true,
                'order' => 5,
            ],
        ];

        foreach ($links as $link) {
            $existing = DB::table('quick_links')->where('title', $link['title'])->first();

            if (!$existing) {
                DB::table('quick_links')->insert(array_merge($link, [
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]));
            }
        }
    }
}
