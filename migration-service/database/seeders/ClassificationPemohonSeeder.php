<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassificationPemohonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Perorangan',
            'Badan Hukum',
            'Kelompok Orang (LSM/Organisasi)'
        ];

        foreach ($data as $value) {
            DB::table('ms_classification_pemohon')->updateOrInsert([
                'name' => $value
            ]);
        }
    }
}
