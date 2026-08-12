<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentDetailProjectSeeder extends Seeder
{
    public function run(): void
    {
        $contents = DB::table('contents')
            ->where('type', 'project')
            ->where('is_section', false)
            ->get();

        if ($contents->isEmpty()) {
            return;
        }

        $layout = DB::table('ms_layout')->where('type', 'project')->first();
        $categories = DB::table('ms_kategori_contents')->get();
        $categoryMap = $categories->pluck('id', 'title')->toArray();
        $datetime = date('Y-m-d H:i:s');

        $projects = [
            [
                'title' => 'Pembuatan Peralatan Mesin Produksi Bahan Baku Logam & Baja',
                'description' => '<p>Proyek ini bertujuan untuk mengembangkan peralatan mesin produksi bahan baku logam dan baja yang mampu memenuhi kebutuhan industri nasional. Dengan mengadopsi teknologi manufaktur modern, proyek ini diharapkan dapat mengurangi ketergantungan pada impor mesin industri.</p>',
                'duration' => '12 Bulan',
                'location' => 'Purwakarta, Jawa Barat',
                'start_date' => date('Y-m-d', strtotime('-6 months')),
                'budget' => 850000000,
                'organization_name' => 'Balai Besar Standardisasi dan Pelayanan Jasa Industri Logam dan Mesin',
                'organization_address' => 'Jl. Pajajaran No. 154, Bandung, Jawa Barat',
                'thumbnail_path' => '/images/proyek-peralatan-mesin.jpg',
                'kategori_content_id' => $categoryMap['Alat Berat'] ?? null,
                'scope' => '<ul><li>Perancangan dan desain struktur peralatan mesin produksi</li><li>Pengembangan sistem otomatisasi lini produksi</li><li>Integrasi kontrol kualitas berbasis sensor</li><li>Pengujian komprehensif dan sertifikasi standar nasional</li><li>Pelatihan operator dan teknisi internal</li></ul>',
                'qualification' => '<ul><li>Memiliki pengalaman minimal 5 tahun di bidang manufaktur logam dan baja</li><li>Memiliki fasilitas workshop dan laboratorium uji yang memadai</li><li>Didukung oleh tim engineer bersertifikasi</li><li>Berpengalaman dalam kerja sama riset teknologi dengan instansi pemerintah</li></ul>',
            ],
            [
                'title' => 'Pengembangan Drone Kustom Berdaya Dorong Tinggi Untuk Pemantauan dan Pengangkutan Beban',
                'description' => '<p>Proyek ini bertujuan untuk mengembangkan drone kustom berdaya dorong tinggi untuk pemantauan dan pengangkutan beban. Drone dirancang untuk operasi di lingkungan industri, pertambangan, dan perkebunan guna memperkuat efisiensi logistik serta monitoring spasial secara presisi.</p>',
                'duration' => '12 Bulan',
                'location' => 'Purwakarta, Jawa Barat',
                'start_date' => date('Y-m-d', strtotime('-3 months')),
                'budget' => 850000000,
                'organization_name' => 'Balai Besar Standardisasi dan Pelayanan Jasa Industri Logam dan Mesin',
                'organization_address' => 'Jl. Pajajaran No. 154, Bandung, Jawa Barat',
                'thumbnail_path' => '/images/proyek-drone.jpg',
                'kategori_content_id' => $categoryMap['Pertahanan dan Keamanan'] ?? null,
                'scope' => '<ul><li>Perancangan dan desain struktur drone kustom berdaya dorong tinggi</li><li>Pengembangan sistem propulsi dan manajemen daya baterai yang optimal</li><li>Integrasi sistem navigasi otonom dan kontrol kendali presisi tinggi</li><li>Pengujian komprehensif serta sertifikasi kelaikan terbang regulatori</li><li>Penyelenggaraan pelatihan intensif untuk operator dan teknisi internal</li></ul>',
                'qualification' => '<ul><li>Memiliki pengalaman minimal 3 tahun di bidang pengembangan drone/UAV komersial</li><li>Memiliki fasilitas workshop perakitan dan laboratorium uji propulsi yang memadai</li><li>Didukung oleh tim engineer bersertifikasi kelaikan kedirgantaraan</li><li>Berpengalaman dalam kerja sama riset teknologi dengan instansi pemerintah</li></ul>',
            ],
            [
                'title' => 'Sistem Informasi Kesehatan Masyarakat Berbasis IoT',
                'description' => '<p>Pengembangan sistem informasi kesehatan masyarakat yang terintegrasi dengan sensor IoT untuk pemantauan kualitas udara, air, dan lingkungan di kawasan industri. Sistem ini mendukung operasional industri yang ramah lingkungan dan berkelanjutan.</p>',
                'duration' => '18 Bulan',
                'location' => 'Jakarta Selatan, DKI Jakarta',
                'start_date' => date('Y-m-d', strtotime('-1 months')),
                'budget' => 1200000000,
                'organization_name' => 'Kementerian Perindustrian RI',
                'organization_address' => 'Jl. Gatot Subroto No.Kav. 52-53, Jakarta Selatan',
                'thumbnail_path' => '/images/proyek-kesehatan-masyarakat.jpg',
                'kategori_content_id' => $categoryMap['Alat Kesehatan dan Farmasi'] ?? null,
                'scope' => '<ul><li>Desain arsitektur sistem pemantauan kesehatan berbasis IoT</li><li>Pengembangan sensor kualitas udara dan air</li><li>Integrasi dashboard monitoring real-time</li><li>Implementasi di 3 kawasan percontohan</li><li>Pelatihan pengelolaan sistem untuk petugas kesehatan</li></ul>',
                'qualification' => '<ul><li>Memiliki pengalaman di bidang pengembangan sistem informasi kesehatan</li><li>Memiliki kemampuan integrasi sensor IoT</li><li>Berpengalaman dalam proyek pemerintah di sektor kesehatan</li></ul>',
            ],
            [
                'title' => 'Portal E-Gov Provinsi untuk Digitalisasi Layanan Publik',
                'description' => '<p>Pengembangan portal layanan pemerintah digital yang terintegrasi untuk memudahkan akses masyarakat terhadap berbagai layanan publik. Portal ini menjadi bagian dari transformasi digital pemerintahan di era industri 4.0.</p>',
                'duration' => '9 Bulan',
                'location' => 'Bandung, Jawa Barat',
                'start_date' => date('Y-m-d', strtotime('+1 months')),
                'budget' => 750000000,
                'organization_name' => 'Dinas Komunikasi dan Informatika Provinsi Jawa Barat',
                'organization_address' => 'Jl. Taman Sari No. 55, Bandung, Jawa Barat',
                'thumbnail_path' => '/images/proyek-egov.jpg',
                'kategori_content_id' => $categoryMap['Elektronik dan Komunikasi'] ?? null,
                'scope' => '<ul><li>Analisis kebutuhan dan perancangan sistem</li><li>Pengembangan frontend dan backend portal</li><li>Integrasi dengan sistem pemerintahan yang ada</li><li>Uji fungsional dan keamanan siber</li><li>Deployment dan pelatihan pengguna</li></ul>',
                'qualification' => '<ul><li>Memiliki pengalaman pengembangan portal e-gov</li><li>Memiliki sertifikasi keamanan siber</li><li>Berpengalaman dengan standar pemerintahan digital</li></ul>',
            ],
            [
                'title' => 'Dashboard Monitoring UMKM Nasional',
                'description' => '<p>Pengembangan dashboard monitoring untuk UMKM nasional yang menyediakan data real-time tentang kinerja UMKM, termasuk produksi, penjualan, dan ekspor. Dashboard ini menjadi alat bantu pengambilan kebijakan pemerintah dalam pemberdayaan UMKM.</p>',
                'duration' => '6 Bulan',
                'location' => 'Jakarta Pusat, DKI Jakarta',
                'start_date' => date('Y-m-d', strtotime('+2 months')),
                'budget' => 500000000,
                'organization_name' => 'Kementerian Koperasi dan UMKM RI',
                'organization_address' => 'Jl. H.R. Rasuna Said Kav. 3-4, Jakarta Selatan',
                'thumbnail_path' => '/images/proyek-umkm.jpg',
                'kategori_content_id' => $categoryMap['Permesinan Perkakas'] ?? null,
                'scope' => '<ul><li>Perancangan UI/UX dashboard monitoring</li><li>Pengembangan backend API dan database</li><li>Integrasi data dari berbagai sumber K/L</li><li>Implementasi visualisasi data interaktif</li><li>Training dan handing over</li></ul>',
                'qualification' => '<ul><li>Memiliki pengalaman pengembangan dashboard analitik</li><li>Memahami ekosistem UMKM nasional</li><li>Berpengalaman dalam integrasi data multi-sumber</li></ul>',
            ],
        ];

        foreach ($contents as $content) {
            foreach ($projects as $project) {
                $slug = Str::slug($project['title']);
                $existing = DB::table('content_detail_projects')->where('slug', $slug)->where('content_id', $content->id)->first();

                if (!$existing) {
                    $projectId = DB::table('content_detail_projects')->insertGetId([
                        'content_id' => $content->id,
                        'title' => $project['title'],
                        'slug' => $slug,
                        'description' => $project['description'],
                        'thumbnail_path' => $project['thumbnail_path'],
                        'is_publicated' => true,
                        'publish_date' => date('Y-m-d', strtotime('-30 days')),
                        'layout_id' => $layout?->id,
                        'has_additional_image' => true,
                        'has_additional_video' => false,
                        'has_additional_file' => false,
                        'has_additional_link' => false,
                        'has_additional_text' => true,
                        'duration' => $project['duration'],
                        'location' => $project['location'],
                        'start_date' => $project['start_date'],
                        'budget' => $project['budget'],
                        'organization_name' => $project['organization_name'],
                        'organization_address' => $project['organization_address'],
                        'kategori_content_id' => $project['kategori_content_id'],
                        'created_at' => $datetime,
                        'updated_at' => $datetime,
                    ]);

                    // Add project images (gallery for detail page)
                    for ($i = 1; $i <= 3; $i++) {
                        DB::table('content_additional_images')->insert([
                            'content_type' => 'App\Models\ContentDetailProject',
                            'content_id' => $projectId,
                            'image_path' => '/images/proyek-' . Str::slug($slug) . '-' . $i . '.jpg',
                            'description' => "Gambar {$i} dari proyek {$project['title']}",
                            'order' => $i,
                            'created_at' => $datetime,
                            'updated_at' => $datetime,
                        ]);
                    }

                    // Add scope text (Lingkup Pekerjaan)
                    DB::table('content_additional_texts')->insert([
                        'content_type' => 'App\Models\ContentDetailProject',
                        'content_id' => $projectId,
                        'title' => 'Lingkup Pekerjaan',
                        'description' => $project['scope'],
                        'order' => 1,
                        'created_at' => $datetime,
                        'updated_at' => $datetime,
                    ]);

                    // Add qualification text (Kualifikasi Kolaborasi)
                    DB::table('content_additional_texts')->insert([
                        'content_type' => 'App\Models\ContentDetailProject',
                        'content_id' => $projectId,
                        'title' => 'Kualifikasi Kolaborasi',
                        'description' => $project['qualification'],
                        'order' => 2,
                        'created_at' => $datetime,
                        'updated_at' => $datetime,
                    ]);
                }
            }
        }
    }
}
