<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailTextSeeder extends Seeder
{
    public function run(): void
    {
        $datetime = date('Y-m-d H:i:s');

        // Text contents for Tentang IMC sub-menus
        $subMenuTexts = [
            ['slug' => 'kontak-kami', 'content' => '<section class="content-section"><h2>Kontak Kami</h2><p><strong>Indonesia Manufacturing Center</strong><br>Kementrian Perindustrian Republik Indonesia</p><p><strong>Headquarters:</strong><br>Jl. Gatot Subroto No.Kav. 52-53<br>Kota Jakarta Selatan<br>DKI Jakarta, Indonesia</p><p><strong>Telepon:</strong> (021) 5255509<br><strong>Email:</strong> layanan-imc@kemenperin.go.id</p></section>'],
        ];

        foreach ($subMenuTexts as $item) {
            $subMenu = DB::table('sub_menus')->where('slug', $item['slug'])->first();
            if (!$subMenu) {
                continue;
            }

            $contentRecord = DB::table('contents')
                ->where('parent_type', 'App\Models\SubMenu')
                ->where('parent_id', $subMenu->id)
                ->where('type', 'text')
                ->first();

            if (!$contentRecord) {
                continue;
            }

            $existing = DB::table('content_detail_texts')->where('content_id', $contentRecord->id)->first();

            if (!$existing) {
                DB::table('content_detail_texts')->insert([
                    'content_id' => $contentRecord->id,
                    'content' => $item['content'],
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
