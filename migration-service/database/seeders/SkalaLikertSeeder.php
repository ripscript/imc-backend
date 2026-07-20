<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkalaLikertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['label' => 'Sangat setuju', 'bobot' => 4.00],
            ['label' => 'Setuju', 'bobot' => 3.00],
            ['label' => 'Tidak setuju', 'bobot' => 2.00],
            ['label' => 'Sangat tidak setuju', 'bobot' => 1.00],
        ];

        foreach ($data as $key => $value) {
            $exists = DB::table('ms_survey_skala_likert')->where('label', $value['label'])->first();
            if (!$exists) {
                DB::table('ms_survey_skala_likert')->insert([
                    'label' => $value['label'],
                    'bobot' => $value['bobot'],
                    'order' => $key + 1
                ]);
            }
        }
    }
}
