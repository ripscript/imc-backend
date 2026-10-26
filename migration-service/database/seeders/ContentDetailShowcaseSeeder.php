<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentDetailShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        $contents = DB::table('contents')
            ->where('type', 'showcase')
            ->where('is_section', false)
            ->get();

        if ($contents->isEmpty()) {
            return;
        }

        $layout = DB::table('ms_layout')->where('type', 'showcase')->first();
        $categories = DB::table('ms_kategori_contents')->get();
        $categoryMap = $categories->pluck('id', 'title')->toArray();
        $datetime = date('Y-m-d H:i:s');

        $showcases = [
            [
                'title' => 'INDORAY Radiographic X-ray Mobile High Frequency 100  IRM',
                'description' => '<p>INDORAY Radiographic X-ray Mobile High Frequency 100 / IRM (IR100HF)</p><p>UNIT<br>Power Input : Single Phase 220 VAC ± 10%<br>Power Consumption : 3500 Watt / 3,5 kW<br>Frequency Range Input : 50/60 Hz<br>Movement : Manual</p><p>GENERATOR<br>Max. Power : 5,6 kW, high Frequency<br>Tube Voltage : 40-125 kV<br>mA Range : 5-100 mA<br>Exposure Time : 0,001-10 sec<br>mAs Range : 0,1-320 mAs<br>Min. Working Frequency : 80 kHz-200 kHz</p><p>X-RAY MONOBLOK<br>Anoda Heat Contact : 30 kJ<br>Focal Spot (focus size) : 0.6 / 1.8<br>Target Angle : 15°<br>Weight : 13± 0.5 kg</p><p>USER INTERFACE<br>Controller : Laptop Touchscreen<br>APR : More Than 650 Pre-Programable Setting (APRs)<br>Switch Expose : Handswitch / Wireless Remote</p>',
                'duration' => '-',
                'location' => 'Purwakarta, Jawa Barat',
                'start_date' => date('Y-m-d', strtotime('-6 months')),
                'budget' => 363370000,
                'organization_name' => 'PT. Poly Jaya Medikal',
                'organization_address' => 'Kp. Kebon Duren, Jl. Ciliwung No. 61 RT 01 RW 01, Kel. Kalimulya, Kec. Cilodong Kota Depok 16413 Jawa Barat - Indonesia',
                'thumbnail_path' => '/images/showcase-peralatan-mesin.jpg',
                'kategori_content_id' => $categoryMap['Alat Kesehatan dan Farmasi'] ?? null,
            ]
        ];

        foreach ($contents as $content) {
            foreach ($showcases as $showcase) {
                $slug = Str::slug($showcase['title']);
                $existing = DB::table('content_detail_showcases')->where('slug', $slug)->where('content_id', $content->id)->first();

                if (! $existing) {
                    $showcaseId = DB::table('content_detail_showcases')->insertGetId([
                        'content_id' => $content->id,
                        'title' => $showcase['title'],
                        'slug' => $slug,
                        'description' => $showcase['description'],
                        'thumbnail_path' => $showcase['thumbnail_path'],
                        'is_publicated' => true,
                        'publish_date' => date('Y-m-d', strtotime('-30 days')),
                        'layout_id' => $layout?->id,
                        'has_additional_image' => true,
                        'has_additional_video' => false,
                        'has_additional_file' => false,
                        'has_additional_link' => false,
                        'has_additional_text' => true,
                        'duration' => $showcase['duration'],
                        'location' => $showcase['location'],
                        'start_date' => $showcase['start_date'],
                        'budget' => $showcase['budget'],
                        'organization_name' => $showcase['organization_name'],
                        'organization_address' => $showcase['organization_address'],
                        'kategori_content_id' => $showcase['kategori_content_id'],
                        'created_at' => $datetime,
                        'updated_at' => $datetime,
                    ]);

                    // Add showcase images (gallery for detail page)
                    for ($i = 1; $i <= 3; $i++) {
                        DB::table('content_additional_images')->insert([
                            'content_type' => 'App\Models\ContentDetailShowcase',
                            'content_id' => $showcaseId,
                            'image_path' => '/images/showcase-'.Str::slug($slug).'-'.$i.'.jpg',
                            'description' => "Gambar {$i} dari showcase {$showcase['title']}",
                            'order' => $i,
                            'created_at' => $datetime,
                            'updated_at' => $datetime,
                        ]);
                    }
                }
            }
        }
    }
}
