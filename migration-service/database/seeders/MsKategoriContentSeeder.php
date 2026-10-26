<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MsKategoriContentSeeder extends Seeder
{
    public function run(): void
    {
        $datetime = date('Y-m-d H:i:s');

        $categories = [
            ['title' => 'Alat Berat', 'is_active' => true],
            ['title' => 'Peralatan Pembangkit Listrik', 'is_active' => true],
            ['title' => 'Pertanian, Kehutanan, Perikanan & Kelautan', 'is_active' => true],
            ['title' => 'Permesinan Perkakas', 'is_active' => true],
            ['title' => 'Pertahanan dan Keamanan', 'is_active' => true],
            ['title' => 'Tekstil, Garmen & Alas Kaki', 'is_active' => true],
            ['title' => 'Elektronik dan Komunikasi', 'is_active' => true],
            ['title' => 'Alat Kesehatan dan Farmasi', 'is_active' => true],
            ['title' => 'Makanan', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            $existing = DB::table('ms_kategori_contents')->where('title', $category['title'])->first();

            if (!$existing) {
                DB::table('ms_kategori_contents')->insert(array_merge($category, [
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]));
            }
        }
    }
}
