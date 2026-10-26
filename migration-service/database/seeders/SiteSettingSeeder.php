<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $datetime = date('Y-m-d H:i:s');

        $existing = DB::table('site_settings')->first();

        if (!$existing) {
            DB::table('site_settings')->insert([
                'address' => 'Headquarters: Jl. Gatot Subroto No.Kav. 52-53, Kota Jakarta Selatan, DKI Jakarta, Indonesia',
                'phone' => '(021) 5255509',
                'email' => 'layanan-imc@kemenperin.go.id',
                'facebook_url' => 'https://facebook.com/kemenperin',
                'instagram_url' => 'https://instagram.com/kemenperin',
                'youtube_url' => 'https://youtube.com/kemenperin',
                'twitter_url' => 'https://twitter.com/kemenperin',
                'wa_number' => '6281234567890',
                'updated_by' => 'system',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }
}
