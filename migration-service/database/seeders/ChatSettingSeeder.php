<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $findSetting = DB::table('chat_settings')->first();
        if (!$findSetting) {
            DB::table('chat_settings')->insert(['updated_by' => 'system']);
        }
    }
}
