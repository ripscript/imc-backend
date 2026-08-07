<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentDetailProjectSeeder extends Seeder
{
    public function run(): void
    {
        $contents = DB::table('contents')
            ->where('type', 'project')
            ->where('is_section', false)
            ->get();

        if ($contents->isEmpty()) {
            return;
        }

        $layout = DB::table('ms_layout')->where('type', 'project')->first();
        $datetime = date('Y-m-d H:i:s');

        $titles = [
            'Platform Kolaborasi Data Terbuka',
            'Sistem Informasi Kesehatan Masyarakat',
            'Portal E-Gov Provinsi',
            'Dashboard Monitoring UMKM',
            'Sistem Manajemen Layanan Publik',
        ];

        foreach ($contents as $content) {
            for ($i = 0; $i < 5; $i++) {
                DB::table('content_detail_projects')->insert([
                    'content_id' => $content->id,
                    'title' => $titles[$i],
                    'slug' => Str::slug($titles[$i]),
                    'description' => "Deskripsi proyek kolaborasi ke-{$i} yang bertujuan untuk meningkatkan layanan publik.",
                    'thumbnail_path' => "/images/proyek-{$i}.jpg",
                    'is_publicated' => true,
                    'publish_date' => date('Y-m-d', strtotime("-{$i} days")),
                    'layout_id' => $layout?->id,
                    'has_additional_image' => false,
                    'has_additional_video' => false,
                    'has_additional_file' => false,
                    'has_additional_link' => false,
                    'has_additional_text' => false,
                    'duration' => ($i + 3) . ' Bulan',
                    'location' => 'Jakarta, Indonesia',
                    'start_date' => date('Y-m-d', strtotime("+{$i} days")),
                    'budget' => ($i + 1) * 100000000,
                    'organization_name' => "Organisasi {$i}",
                    'organization_address' => "Alamat organisasi ke-{$i}",
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
