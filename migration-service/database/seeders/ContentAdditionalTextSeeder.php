<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentAdditionalTextSeeder extends Seeder
{
    public function run(): void
    {
        $datetime = date('Y-m-d H:i:s');

        // Tentang IMC > Profil IMC
        $this->seedSubMenuAdditionalTexts($datetime, 'profil-imc', [
            [
                'title' => 'Profil IMC',
                'order' => 1,
                'description' => '<p>Indonesia Manufacturing Center (IMC) didirikan sebagai upaya strategis untuk meningkatkan Tingkat Komponen Dalam Negeri (TKDN) dan mengurangi ketergantungan pada impor oleh industri dalam negeri. IMC hadir untuk memperkuat daya saing industri nasional melalui kolaborasi antar pemangku kepentingan, guna mewujudkan Indonesia sebagai pemasok utama kebutuhan industri manufaktur.</p><p>Sebagai pusat kolaborasi pengembangan dan pemanfaatan teknologi industri, IMC dirancang menjadi one-stop service yang menghubungkan dunia industri, perguruan tinggi, lembaga riset dan pengembangan, masyarakat, serta Pemerintah Pusat dan Daerah. Kolaborasi ini dilandasi pendekatan Pentahelix untuk membangun ekosistem industri yang kuat, mandiri, dan berdaya saing tinggi.</p><p>IMC memiliki konsep operasional 3M (Machine Making Machine) - yaitu kemampuan untuk memproduksi mesin yang dapat membuat mesin lainnya - sebagai simbol kemandirian teknologi nasional.</p>',
            ],
            [
                'title' => 'Tujuan Strategis IMC',
                'order' => 2,
                'description' => '<p>IMC memiliki tujuan strategis sebagai berikut:</p><ul><li>Menjadi One-Stop Services bagi pengembangan teknologi dan inovasi industri</li><li>Mendorong produk substitusi impor dalam sektor manufaktur nasional</li><li>Meningkatkan kinerja dan daya saing industri manufaktur</li><li>Menciptakan technopreneur dan pelaku industri baru</li><li>Memperkuat rantai pasok nasional berbasis teknologi</li></ul><p>Untuk mendukung kinerjanya, IMC dilengkapi dengan: sarana dan prasarana modern, kelembagaan yang adaptif, Sumber Daya Manusia (SDM) yang kompeten, mesin dan peralatan industri manufaktur, serta Sistem ICT (Information and Communication Technology) terintegrasi.</p>',
            ],
            [
                'title' => 'Sektor Prioritas',
                'order' => 3,
                'description' => '<p>IMC memberikan layanan dan pengembangannya pada 8 sektor industri prioritas nasional, yaitu:</p><ol><li>Alat berat dan Konstruksi</li><li>Peralatan Pembangkit Listrik dan Penunjang Migas</li><li>Pertanian, Kehutanan, Perikanan dan Kelautan</li><li>Permesinan Perkakas</li><li>Pertahanan dan Keamanan</li><li>Tekstil, Garmen dan Alas Kaki</li><li>Elektronik dan Komunikasi</li><li>Alat kesehatan dan Farmasi</li></ol><p>Diharapkan IMC menjadi katalis utama dalam membangun industri manufaktur nasional yang berkelanjutan dan berdaya saing global.</p>',
            ],
        ]);

        // Tentang IMC > Tugas dan Fungsi
        $this->seedSubMenuAdditionalTexts($datetime, 'tugas-dan-fungsi', [
            [
                'title' => 'Tugas dan Fungsi',
                'order' => 1,
                'description' => '<section class="content-section"><h2>Tugas dan Fungsi</h2><p>IMC memiliki tugas dan fungsi sebagai berikut:</p><ul><li>Menjadi one-stop services bagi pengembangan teknologi dan inovasi industri</li><li>Mendorong produk substitusi impor dalam sektor manufaktur nasional</li><li>Meningkatkan kinerja dan daya saing industri manufaktur</li><li>Menciptakan technopreneur dan pelaku industri baru</li><li>Memperkuat rantai pasok nasional berbasis teknologi</li></ul></section>',
            ],
        ]);

        // Tentang IMC > Visi dan Misi
        $this->seedSubMenuAdditionalTexts($datetime, 'visi-dan-misi', [
            [
                'title' => 'Visi dan Misi',
                'order' => 1,
                'description' => '<section class="content-section"><h2>Visi dan Misi</h2><h3>Visi</h3><p>Menjadi pusat unggulan pengembangan teknologi manufaktur nasional yang mandiri dan berdaya saing global.</p><h3>Misi</h3><ul><li>Memperkuat kemandirian teknologi manufaktur nasional</li><li>Membangun ekosistem kolaborasi industri yang terstruktur</li><li>Mengakselerasi hilirisasi riset menjadi produk industri</li><li>Meningkatkan kompetensi sumber daya manusia industri</li><li>Memperluas akses pasar bagi produk manufaktur nasional</li></ul></section>',
            ],
        ]);

        // Tentang IMC > Sejarah IMC
        $this->seedSubMenuAdditionalTexts($datetime, 'sejarah-imc', [
            [
                'title' => 'Sejarah IMC',
                'order' => 1,
                'description' => '<section class="content-section"><h2>Sejarah IMC</h2><p>Berdasarkan Surat Tugas Menteri Perindustrian Nomor 493 tanggal 26 Maret 2025, Direktur Jenderal Industri Logam, Mesin, Alat Transportasi, dan Elektronika (Dirjen ILMate) diberikan penugasan untuk mengelola dan mengoperasionalkan Indonesia Manufacturing Center (IMC) yang berlokasi di Jalan Warung Jeruk - Citeko, Desa Pamoyanan, Kecamatan Plered, Kabupaten Purwakarta, Provinsi Jawa Barat.</p><p>Pada tahun 2025, IMC tengah memasuki tahap penting dalam perjalanan kelembagaan, yakni penyusunan Unit Pelaksana Teknis (UPT) Balai Teknologi Industri Manufaktur (BTMI). Proses ini dilaksanakan bersama Kementerian PANRB sebagai mitra strategis dalam penataan organisasi pemerintah yang adaptif, efisien, dan berorientasi pada pelayanan publik.</p><p>Pada Tanggal 5 Januari 2026, telah disetujui Pembentukan Balai Teknologi Industri Manufaktur (BTIM) Oleh Kemenpan RB.</p></section>',
            ],
        ]);

        // Tentang IMC > Layanan Kami
        $this->seedSubMenuAdditionalTexts($datetime, 'layanan-kami', [
            [
                'title' => 'Layanan Kami',
                'order' => 1,
                'description' => '<section class="content-section"><h2>Layanan Kami</h2><p>Untuk mendukung kinerjanya, IMC dilengkapi dengan:</p><ul><li>Sarana dan prasarana modern</li><li>Kelembagaan yang adaptif</li><li>Sumber Daya Manusia (SDM) yang kompeten</li><li>Mesin dan peralatan industri manufaktur</li><li>Sistem ICT (Information and Communication Technology) terintegrasi</li></ul><p>Layanan utama IMC meliputi:</p><ul><li><strong>Konsultasi Industri</strong> - Pendampingan strategis pengembangan dan peningkatan kapasitas industri nasional</li><li><strong>Sertifikasi Produk</strong> - Fasilitasi sertifikasi TKDN dan standar mutu produk industri dalam negeri</li><li><strong>Pelatihan SDM</strong> - Program vokasi dan pelatihan tenaga kerja industri berbasis teknologi modern</li><li><strong>Riset & Pengembangan</strong> - Kolaborasi riset inovasi produk dan teknologi manufaktur mutakhir bersama institusi</li></ul></section>',
            ],
        ]);
    }

    private function seedSubMenuAdditionalTexts(string $datetime, string $subMenuSlug, array $sections): void
    {
        $content = DB::table('contents as c')
            ->join('sub_menus as s', 's.id', '=', 'c.parent_id')
            ->where('c.parent_type', 'App\Models\SubMenu')
            ->where('c.type', 'image_carousel')
            ->where('s.slug', $subMenuSlug)
            ->select('c.id')
            ->first();

        if (! $content) {
            return;
        }

        foreach ($sections as $section) {
            $exists = DB::table('content_additional_texts')
                ->where('content_type', 'App\Models\Content')
                ->where('content_id', $content->id)
                ->where('order', $section['order'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('content_additional_texts')->insert([
                'content_type' => 'App\Models\Content',
                'content_id' => $content->id,
                'title' => $section['title'],
                'description' => $section['description'],
                'order' => $section['order'],
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }
}