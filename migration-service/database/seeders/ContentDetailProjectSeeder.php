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
                'title' => 'Pembuatan Prototipe Ventilator Produksi Dalam Negeri VENINDO',
                'description' => '<p><strong>Deskripsi Pekerjaan:</strong>&nbsp;<br>Pandemi COVID-19 menimbulkan lonjakan kebutuhan ventilator di berbagai rumah sakit di Indonesia. Untuk menjawab tantangan ini, dibentuklah&nbsp;Tim Pengembangan Ventilator Yogyakarta yang melibatkan sinergi&nbsp;akademisi, tenaga medis, industri, pemerintah, dan mitra swasta. Proyek kolaborasi ini menghasilkan prototipe ventilator buatan Indonesia dengan nama&nbsp;VENINDO, yang dikembangkan dalam tiga tipe:&nbsp;Rapid Deploy, HFNC Emergency, dan High-End ICU Ventilator.</p><p>Pengembangan dilakukan melalui pendekatan&nbsp;<i>reverse engineering</i>, uji coba laboratorium, pengujian klinis, serta pemenuhan regulasi produksi dan izin edar. Proyek ini menjadi bukti nyata bahwa kolaborasi lintas sektor mampu melahirkan inovasi kesehatan strategis yang berdampak luas bagi kemandirian bangsa.</p><p><strong>Tujuan Umum :</strong><br>Mengembangkan teknologi ventilator produksi dalam negeri yang&nbsp;cepat diproduksi, terjangkau, dan sesuai standar internasional, untuk mendukung penanganan darurat COVID-19 serta kemandirian alat kesehatan nasional.</p><p><strong>Tujuan Khusus :</strong></p><ul><li>Membuat prototipe ventilator buatan Indonesia.</li><li>Memenuhi regulasi terkait izin produksi, izin edar, dan standar mutu.</li><li>Menyiapkan&nbsp;<i>pilot production</i> sebagai langkah menuju produksi massal.</li></ul><p><strong>Sasaran :</strong></p><ul><li>Tersedianya prototipe ventilator VENINDO yang sesuai standar.</li><li>Terbitnya sertifikat produksi dan merek dagang.</li><li>Tersedianya fasilitas produksi di UGM Science Techno Park.</li><li>Terjalinnya kerja sama distribusi dengan perusahaan penyedia alat kesehatan.</li></ul><p><strong>Kualifikasi Kolaborasi:</strong>&nbsp;</p><p>Kualifikasi kolaborasi dalam pengembangan ventilator produksi dalam negeri dibangun melalui sinergi antara unsur akademisi, tenaga medis, industri, pemerintah, dan mitra swasta. Akademisi memberikan kekuatan pada sisi riset, desain teknologi, serta penyediaan fasilitas uji dan produksi awal. Dengan latar belakang keilmuan yang kuat, rancangan ventilator dapat memenuhi standar internasional sekaligus disesuaikan dengan kebutuhan lokal.</p><p>Tenaga medis menghadirkan kompetensi klinis yang menjadi acuan utama dalam memastikan fungsi dan keselamatan alat. Melalui uji coba, simulasi, hingga pelaksanaan uji klinis, masukan dari dokter dan tim kesehatan menjadi fondasi perbaikan berkelanjutan. Hal ini memastikan bahwa ventilator yang dikembangkan tidak hanya tepat secara teknis, tetapi juga relevan dengan kebutuhan pelayanan pasien di lapangan.</p><p>Industri berperan dalam mewujudkan hasil riset menjadi produk nyata melalui kemampuan manufaktur, perakitan, dan integrasi sistem. Pemerintah memperkuat kolaborasi ini melalui dukungan regulasi, pengujian mutu, serta pengurusan perizinan yang diperlukan. Mitra swasta turut melengkapi dengan sumber daya, teknologi tambahan, serta dukungan distribusi. Kombinasi kualifikasi ini menciptakan ekosistem kolaborasi yang kokoh, sehingga prototipe ventilator dapat berkembang menjadi produk yang layak diproduksi massal dan digunakan secara luas di fasilitas kesehatan.</p>',
                'duration' => '-',
                'location' => 'Purwakarta, Jawa Barat',
                'start_date' => '2025-09-11',
                'budget' => 1,
                'organization_name' => 'IMC',
                'organization_address' => '-',
                'thumbnail_path' => '/images/proyek-peralatan-mesin.jpg',
                'kategori_content_id' => $categoryMap['Alat Kesehatan dan Farmasi'] ?? null,
            ],
            [
                'title' => 'Peningkatan Kualitas Produk Coran Industri Melalui Pelatihan dan Supervisi di Pabrik Mesin Tenera',
                'description' => '<p><strong>Periode</strong>: Juni – 2025</p><p><strong>Deskripsi</strong>:&nbsp;</p><p>Balai Besar Logam dan Mesin (BBLM) berkomitmen mendukung kemajuan industri manufaktur nasional dengan meningkatkan kualitas produk coran logam. Melalui kegiatan pelatihan dan supervisi teknologi pengecoran di Pabrik Mesin Tenera (PMT) Medan, proyek ini bertujuan membantu peningkatan mutu produksi coran serta kompetensi tenaga kerja industri.</p><p><strong>Peran Balai Besar Logam dan Mesin :</strong></p><ul><li>Memberikan pelatihan teknis intensif dan menyeluruh meliputi seluruh tahapan proses pengecoran logam.</li><li>Melakukan supervisi langsung di lini produksi untuk memastikan pembelajaran aplikatif.</li><li>Membawakan materi teknis komprehensif, termasuk teknologi pembuatan cetakan, perancangan sistem penambah, prosedur peleburan dan perlakuan panas, serta pengendalian kualitas.</li><li>Mendorong modernisasi proses dengan pemanfaatan sistem <i>continuous mixer</i> dan teknik reklamasi pasir yang efisien, serta memperkenalkan peralatan <i>treatment</i> cetakan canggih untuk kualitas dan keberlanjutan.</li></ul><p>&nbsp;</p><p><strong>Hasil / Impact&nbsp;</strong></p><ul><li>Peningkatan keterampilan operator dan <i>engineer</i> PMT secara signifikan; proses produksi lebih terstruktur dan terstandarisasi.</li><li>Mutu produk coran meningkat drastis dari sisi kualitas permukaan, akurasi bentuk, hingga konsistensi hasil, berpotensi menurunkan <i>reject rate</i> dan meningkatkan daya saing produk.</li><li>Mendorong manajemen PMT untuk mengembangkan standar operasional internal dan mempertimbangkan peningkatan fasilitas produksi.</li><li>Kontribusi nyata BBLM dalam memperkuat industri pengecoran nasional melalui peningkatan mutu, efisiensi, dan adopsi teknologi yang relevan dengan kebutuhan pasar global.</li></ul><p>&nbsp;</p><p><strong>Kualifikasi Kolaborasi </strong>:</p><ul><li>Pabrik Mesin Tenera (PMT) Medan - contoh nyata keberhasilan kolaborasi BBLM dalam meningkatkan kapabilitas produksi manufaktur. PMT mendapatkan manfaat langsung berupa peningkatan kualitas produksi dan SDM yang siginfikan.</li></ul>',
                'duration' => '-',
                'location' => 'Bandung, Jawa Barat',
                'start_date' => '2025-08-28',
                'budget' => 10,
                'organization_name' => 'Balai Besar Standardisasi dan Pelayanan Jasa Industri Logam dan Mesin',
                'organization_address' => 'Jl. Sangkuriang No. 12 – Bandung 40135.',
                'thumbnail_path' => '/images/proyek-drone.jpg',
                'kategori_content_id' => $categoryMap['Elektronik dan Komunikasi'] ?? null,
            ],
            [
                'title' => 'Pengembangan Drone Kustom Berdaya Dorong Tinggi untuk Pemantauan dan Pengangkutan Beban',
                'description' => '<h3><strong>Deskripsi Pekerjaan:&nbsp;</strong></h3><p>Proyek ini bertujuan untuk merancang dan memproduksi drone kustom dengan daya dorong tinggi, yang dapat digunakan untuk berbagai aplikasi seperti pemantauan area luas, pengangkutan beban berat, dan surveilans di sektor pertanian, konstruksi, logistik, dan keamanan. Kegiatan meliputi desain drone sesuai kebutuhan spesifik pelanggan, pemilihan komponen performa tinggi, pembuatan prototipe, pengujian kinerja, serta penyediaan paket layanan tambahan seperti pelatihan dan purna jual.</p><h3><strong>Lingkup Pekerjaan:</strong></h3><ul><li>Kustomisasi teknis drone sesuai kebutuhan pengguna</li><li>Integrasi sistem daya dorong tinggi dan kontrol kendali</li><li>Pengujian dan kalibrasi performa drone</li><li>Pengembangan paket layanan pelatihan dan perawatan</li><li>Kolaborasi dengan mitra industri dan institusi riset</li></ul><h3><strong>Kualifikasi Kolaborasi:</strong></h3><ul><li>Pengalaman pembuatan dan kustomisasi drone atau UAV</li><li>Keahlian dalam sistem kendali dan elektronik drone</li><li>Kapasitas untuk memproduksi dan melakukan uji coba drone performa tinggi</li><li>Kemampuan integrasi perangkat lunak pengendali penerbangan</li><li>Keikutsertaan dalam riset atau pengembangan produk teknologi tinggi</li></ul>',
                'duration' => '-',
                'location' => 'Bandung, Jawa Barat',
                'start_date' => '2025-08-11',
                'budget' => 150000000,
                'organization_name' => 'Balai Besar Standardisasi dan Pelayanan Jasa Industri Logam dan Mesin',
                'organization_address' => 'Jl. Sangkuriang No. 12 – Bandung 40135.',
                'thumbnail_path' => '/images/proyek-kesehatan-masyarakat.jpg',
                'kategori_content_id' => $categoryMap['Elektronik dan Komunikasi'] ?? null,
            ],
            [
                'title' => 'Pengembangan Mesin CNC 3Axis G 40X untuk Peningkatan Kapasitas Produksi Metalworking',
                'description' => '<h3><strong>Deskripsi Pekerjaan:</strong>&nbsp;<br>&nbsp;</h3><p>Mesin CNC 3-axis tipe G 4.0-X dikembangkan untuk meningkatkan kapasitas produksi komponen logam pada industri manufaktur. Proses meliputi penyediaan material rangka, perakitan komponen mekanik, instalasi spindle berkecepatan tinggi, kalibrasi sistem kontrol, dan pengujian operasional mesin. Mesin ini memiliki fitur seperti resolusi 0,03 mm pada sumbu X, Y, dan Z serta kecepatan spindle 15.000 rpm.</p><p><strong>Contoh Kegiatan Utama&nbsp;</strong></p><ul><li>Desain dan fabrikasi rangka besi hollow 100 × 100 × 4 mm.</li><li>Pemasangan sistem spindle dan motor hybrid closed-loop stepper.</li><li>Implementasi controller MACH3 dengan interface touchscreen.</li><li>Integrasi konektivitas Wi-Fi dan Bluetooth untuk monitoring jarak jauh.</li><li>Pelatihan operator dalam penggunaan dan maintenance mesin.</li></ul><h3><strong>Kualifikasi Kolaborasi:</strong>&nbsp;</h3><ul><li>Pengalaman dalam fabrikasi mesin CNC atau perangkat presisi tinggi.</li><li>Memiliki rekam jejak di proyek pengembangan mesin berbasis kontrol digital.</li><li>Pemahaman mendalam tentang sistem kontrol stepper dan mekanik presisi.</li></ul>',
                'duration' => '-',
                'location' => 'Bandung, Jawa Barat',
                'start_date' => '2025-08-11',
                'budget' => 300000000,
                'organization_name' => 'Balai Besar Standardisasi dan Pelayanan Jasa Industri Logam dan Mesin',
                'organization_address' => 'Jl. Sangkuriang No. 12 – Bandung 40135.',
                'thumbnail_path' => '/images/proyek-egov.jpg',
                'kategori_content_id' => $categoryMap['Makanan'] ?? null,
            ],
        ];

        foreach ($contents as $content) {
            foreach ($projects as $project) {
                $slug = Str::slug($project['title']);
                $existing = DB::table('content_detail_projects')->where('slug', $slug)->where('content_id', $content->id)->first();

                if (! $existing) {
                    $projectId = DB::table('content_detail_projects')->insertGetId([
                        'content_id' => $content->id,
                        'title' => $project['title'],
                        'slug' => $slug,
                        'description' => $project['description'],
                        'thumbnail_path' => $project['thumbnail_path'],
                        'is_publicated' => true,
                        'publish_date' => date('Y-m-d', strtotime('-30 days')),
                        'status' => 'completed',
                        'progress' => 100,
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
                            'image_path' => '/images/proyek-'.Str::slug($slug).'-'.$i.'.jpg',
                            'description' => "Gambar {$i} dari proyek {$project['title']}",
                            'order' => $i,
                            'created_at' => $datetime,
                            'updated_at' => $datetime,
                        ]);

                        DB::table('content_detail_projects')->where('id', $projectId)->update([
                            'project_no' => 'IMC.'.date('Y').'-'.str_pad((string) $projectId, 3, '0', STR_PAD_LEFT),
                        ]);
                    }
                }
            }
        }
    }
}
