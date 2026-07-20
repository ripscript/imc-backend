<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerwakilanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perwakilanMissingFullname = DB::table('ms_perwakilan')->whereNull('full_name')->get();
        foreach ($perwakilanMissingFullname as $value) {
            DB::table('ms_perwakilan')->where(['id' => $value->id])->update([
                'full_name' => $value->type ." ". $value->name
            ]);
        }
    }
}
