<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ms_variable_template_email')->delete();
        $variables = [];
        foreach ($variables as $template => $value) {
            foreach ($value as $v) {
                $exists = DB::table('ms_variable_template_email')
                    ->where('name', $v)
                    ->where('template_email', $template)
                    ->first();
                if (!$exists) {
                    DB::table('ms_variable_template_email')->insert([
                        'name' => $v,
                        'template_email' => $template
                    ]);
                }
            }
        }

        $templates = [
            'Pengirim',
            'Header/Kop Email',
        ];
        foreach ($templates as $value) {
            DB::table('template_emails')->updateOrInsert([
                'name' => $value
            ]);
        }

        DB::table('template_emails')->whereNotIn('name', $templates)->delete();
        DB::table('ms_variable_template_email')->whereNotIn('template_email', $templates)->delete();
    }
}
