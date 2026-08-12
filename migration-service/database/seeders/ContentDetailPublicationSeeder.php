<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailPublicationSeeder extends Seeder
{
    public function run(): void
    {
        $content = DB::table('contents')
            ->where('type', 'publication')
            ->where('is_section', false)
            ->first();

        if (!$content) {
            return;
        }

        $layout = DB::table('ms_layout')->where('type', 'publication')->first();
        $datetime = date('Y-m-d H:i:s');

        $categories = DB::table('ms_kategori_contents')->get();
        $categoryMap = $categories->pluck('id', 'title')->toArray();

        $publications = [
            [
                'title' => 'EV Mining Outlook: Elektrifikasi Pertambangan Bisa Sukses, Kemenperin Ungkap Syaratnya',
                'slug' => 'ev-mining-outlook-elektrifikasi-pertambangan',
                'headline' => 'Elektrifikasi pertambangan menuju keberlanjutan industri nasional',
                'description' => '<p>Indonesia Manufacturing Center (IMC) mendorong transisi energi di sektor pertambangan melalui elektrifikasi alat berat. Dengan konsep 3M (Machine Making Machine), IMC siap menjadi katalis utama dalam membangun industri manufaktur nasional yang berkelanjutan dan berdaya saing global.</p><p>Sektor pertambangan merupakan salah satu industri prioritas nasional yang membutuhkan inovasi teknologi untuk meningkatkan efisiensi operasional. Elektrifikasi alat berat menjadi solusi strategis dalam mengurangi ketergantungan pada bahan bakar fosil sekaligus mendukung target net-zero emission Indonesia.</p>',
                'publish_date' => date('Y-m-d', strtotime('-10 days')),
                'is_slider' => true,
                'kategori_content_id' => $categoryMap['Alat Berat'] ?? null,
            ],
            [
                'title' => 'Pembuatan Peralatan Mesin Produksi Bahan Baku Logam & Baja',
                'slug' => 'pembuatan-peralatan-mesin-produksi-bahan-baku-logam-baja',
                'headline' => 'Penguatan rantai pasok logam nasional melalui teknologi mesin industri',
                'description' => '<p>IMC berperan aktif dalam pengembangan peralatan mesin produksi bahan baku logam dan baja. Proyek ini merupakan bagian dari upaya membangun kemandirian teknologi nasional dan mengurangi ketergantungan pada impor mesin industri.</p><p>Melalui kolaborasi dengan akademisi, riset, asosiasi, dan pelaku industri, IMC menghadirkan solusi manufaktur yang tepat guna untuk mendukung hilirisasi industri logam nasional.</p>',
                'publish_date' => date('Y-m-d', strtotime('-15 days')),
                'is_slider' => false,
                'kategori_content_id' => $categoryMap['Alat Berat'] ?? null,
            ],
            [
                'title' => 'Inovasi Teknologi Mesin Pertanian untuk Ketahanan Pangan Nasional',
                'slug' => 'inovasi-teknologi-mesin-pertanian',
                'headline' => 'Mesin pertanian modern untuk efisiensi produktivitas pangan',
                'description' => '<p>IMC mengembangkan teknologi mesin pertanian yang dapat meningkatkan produktivitas dan efisiensi di sektor pertanian, kehutanan, perikanan, dan kelautan. Dengan pendekatan Pentahelix, IMC menghubungkan dunia industri, perguruan tinggi, lembaga riset, masyarakat, dan pemerintah untuk membangun ekosistem industri yang kuat.</p>',
                'publish_date' => date('Y-m-d', strtotime('-20 days')),
                'is_slider' => false,
                'kategori_content_id' => $categoryMap['Pertanian, Kehutanan, Perikanan & Kelautan'] ?? null,
            ],
            [
                'title' => 'Pengembangan Drone Kustom Berdaya Dorong Tinggi Untuk Pemantauan dan Pengangkutan Beban',
                'slug' => 'pengembangan-drone-kustom-berdaya-dorong-tinggi',
                'headline' => 'Drone industri untuk operasi pemantauan dan logistik presisi',
                'description' => '<p>Proyek ini bertujuan untuk mengembangkan drone kustom berdaya dorong tinggi untuk pemantauan dan pengangkutan beban. Drone dirancang untuk operasi di lingkungan industri, pertambangan, dan perkebunan guna memperkuat efisiensi logistik serta monitoring spasial secara presisi.</p><p>Lingkup pekerjaan meliputi perancangan struktur drone, pengembangan sistem propulsi, integrasi sistem navigasi otonom, pengujian komprehensif, serta pelatihan intensif untuk operator dan teknisi internal.</p>',
                'publish_date' => date('Y-m-d', strtotime('-25 days')),
                'is_slider' => false,
                'kategori_content_id' => $categoryMap['Pertahanan dan Keamanan'] ?? null,
            ],
            [
                'title' => 'Indooray Radiographic X-ray Mobile High Frequency 100 / IRM',
                'slug' => 'indooray-radiographic-x-ray-mobile-high-frequency',
                'headline' => 'Mesin rontgen mobile untuk layanan kesehatan di daerah terpencil',
                'description' => '<p>IMC mengembangkan mesin rontgen mobile berfrekuensi tinggi untuk mendukung akses layanan kesehatan di seluruh Indonesia. Inovasi ini merupakan bagian dari komitmen IMC dalam memperkuat sektor alat kesehatan dan farmasi nasional.</p><p>Teknologi ini memungkinkan pemindaian radiografi berkualitas tinggi dapat diakses di daerah terpencil dan pulau-pulau kecil yang membutuhkan layanan kesehatan.</p>',
                'publish_date' => date('Y-m-d', strtotime('-30 days')),
                'is_slider' => false,
                'kategori_content_id' => $categoryMap['Alat Kesehatan dan Farmasi'] ?? null,
            ],
        ];

        foreach ($publications as $pub) {
            $existing = DB::table('content_detail_publications')->where('slug', $pub['slug'])->first();

            if (!$existing) {
                DB::table('content_detail_publications')->insert([
                    'content_id' => $content->id,
                    'title' => $pub['title'],
                    'slug' => $pub['slug'],
                    'headline' => $pub['headline'],
                    'description' => $pub['description'],
                    'is_publicated' => true,
                    'thumbnail_path' => '/images/publication-' . $pub['slug'] . '.jpg',
                    'publish_date' => $pub['publish_date'],
                    'has_additional_image' => false,
                    'has_additional_video' => false,
                    'has_additional_file' => false,
                    'has_additional_link' => false,
                    'has_additional_text' => false,
                    'layout_id' => $layout?->id,
                    'is_slider' => $pub['is_slider'],
                    'kategori_content_id' => $pub['kategori_content_id'],
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
