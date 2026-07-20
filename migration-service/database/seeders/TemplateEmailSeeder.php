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
        $variables = [
            'Pendaftaran Pemohon' => ['otp', 'nama'],
            'Lupa Password' => ['nama', 'link', 'token'],
            'Pemberitahuan Diterima' => ['tgl_permohonan', 'nama', 'alamat', 'no_telepon', 'email', 'no_permohonan', 'tgl_diproses'],
            'Penolakan Permohonan' => ['tgl_diproses', 'no_permohonan', 'nama', 'alamat', 'no_telepon', 'email', 'alasan_ditolak', 'tgl_ditolak', 'tgl_permohonan'],
            'Jawaban Permohonan' => ['tgl_diproses', 'nama', 'alamat', 'no_telepon', 'email', 'tanggapan', 'no_permohonan', 'tgl_dijawab', 'nama_institusi', 'tgl_permohonan'],
            'Persetujuan Keberatan' => ['tgl_keberatan', 'no_keberatan', 'no_permohonan', 'nama', 'alamat', 'no_telepon', 'email', 'alasan_keberatan_pemohon', 'detail_keberatan_pemohon', 'tgl_diproses'],
            'Tanggapan Keberatan' => ['tgl_keberatan', 'no_keberatan', 'no_permohonan', 'nama', 'alamat', 'email', 'no_telepon', 'tgl_dijawab', 'tanggapan', 'alasan_keberatan_pemohon', 'detail_keberatan_pemohon'],
            'Tambahan Waktu Proses Permohonan' => ['tgl_diproses', 'no_permohonan', 'nama', 'alamat', 'no_telepon', 'email', 'alasan_penambahan_waktu', 'tgl_dikirim', 'nama_institusi', 'jumlah_penambahan_hari', 'tgl_permohonan']
        ];
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
            'Pendaftaran Pemohon',
            'Lupa Password',
            'Pemberitahuan Diterima',
            'Penolakan Permohonan',
            'Jawaban Permohonan',
            'Persetujuan Keberatan',
            'Tanggapan Keberatan',
            'Tambahan Waktu Proses Permohonan'
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
