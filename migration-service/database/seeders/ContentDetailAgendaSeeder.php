<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailAgendaSeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'agenda')
            ->where('is_section', false)
            ->first();

        if (! $content) {
            return;
        }

        $datetime = date('Y-m-d H:i:s');

        for ($i = 1; $i <= 5; $i++) {
            $startDate = date('Y-m-d H:i:s', strtotime("+{$i} days"));
            $endDate = date('Y-m-d H:i:s', strtotime("+{$i} days +3 hours"));

            DB::table('content_detail_agendas')->insert([
                'content_id' => $content->id,
                'is_range' => true,
                'datetime_start' => $startDate,
                'datetime_end' => $endDate,
                'event_name' => "Kegiatan IMC {$i}",
                'is_active' => true,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }
}
