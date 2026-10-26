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
                'title' => 'Tentang IMC',
                'order' => 1,
                'description' => '<p><strong>Indonesia Manufacturing Center (IMC) </strong>didirikan sebagai upaya strategis untuk meningkatkan Tingkat Komponen Dalam Negeri (TKDN) dan mengurangi ketergantungan pada impor oleh industri dalam negeri. IMC hadir untuk memperkuat daya saing industri nasional melalui kolaborasi antar pemangku kepentingan, guna mewujudkan Indonesia sebagai pemasok utama kebutuhan industri manufaktur.</p><p>Sebagai <strong>pusat kolaborasi pengembangan dan pemanfaatan teknologi industri</strong>, IMC dirancang menjadi <i>one-stop service</i>yang menghubungkan dunia industri, perguruan tinggi, lembaga riset dan pengembangan, masyarakat, serta Pemerintah Pusat dan Daerah. Kolaborasi ini dilandasi pendekatan <i>Pentahelix </i>untuk membangun ekosistem industri yang kuat, mandiri, dan berdaya saing tinggi.</p><p>IMC memiliki konsep operasional <strong>3M (</strong><i><strong>Machine Making Machine</strong></i><strong>)</strong>— yaitu kemampuan untuk memproduksi mesin yang dapat membuat mesin lainnya — sebagai simbol kemandirian teknologi nasional.</p>',
            ],
            [
                'title' => 'Tujuan Strategis IMC',
                'order' => 2,
                'description' => '<ul><li>Menjadi One-Stop Services bagi pengembangan teknologi dan inovasi industri</li><li>Mendorong produk substitusi impor dalam sektor manufaktur nasional</li><li>Meningkatkan kinerja dan daya saing industri manufaktur</li><li>Menciptakan technopreneur dan pelaku industri baru</li><li>Memperkuat rantai pasok nasional berbasis teknologi</li></ul><p>Untuk mendukung kinerjanya, IMC dilengkapi dengan:</p><ul><li>Sarana dan prasarana modern</li><li>Kelembagaan yang adaptif</li><li>Sumber Daya Manusia (SDM) yang kompeten</li><li>Mesin dan peralatan industri manufaktur</li><li>Sistem ICT (Information and Communication Technology) terintegrasi</li></ul>',
            ],
            [
                'title' => 'Sektor Prioritas',
                'order' => 3,
                'description' => '<p>IMC memberikan layanan dan pengembangannya pada <strong>8 sektor industri prioritas nasional</strong>, yaitu:</p><ol><li>Alat berat dan Konstruksi</li><li>Peralatan Pembangkit Listrik dan Penunjang Migas</li><li>Pertanian, Kehutanan, Perikanan dan Kelautan</li><li>Permesinan Perkakas</li><li>Pertahanan dan Keamanan</li><li>Tekstil, Garmen dan Alas Kaki</li><li>Elektronik dan Komunikasi</li><li>Alat kesehatan dan Farmasi</li></ol><p>Diharapkan IMC menjadi katalis utama dalam membangun industri manufaktur nasional yang berkelanjutan dan berdaya saing global.</p><p>Berdasarkan Surat Tugas Menteri Perindustrian Nomor 493 tanggal 26 Maret 2025, Direktur Jenderal Industri Logam, Mesin, Alat Transportasi, dan Elektronika (Dirjen ILMATE) diberikan penugasan untuk mengelola dan mengoperasionalisasikan Indonesia Manufacturing Center (IMC) yang berlokasi di Jalan Warung Jeruk – Citeko, Desa Pamoyanan, Kecamatan Plered, Kabupaten Purwakarta, Provinsi Jawa Barat.</p><p><br>Pada tahun 2025, IMC tengah memasuki tahap penting dalam perjalanan kelembagaan, yakni penyusunan <strong>Unit Pelaksana Teknis (UPT) Balai Teknologi Industri Manufaktur (BTMI)</strong>. Proses ini dilaksanakan bersama Kementerian PANRB sebagai mitra strategis dalam penataan organisasi pemerintah yang adaptif, efisien, dan berorientasi pada pelayanan publik.&nbsp;</p><p>Pada Tanggal 5 Januari 2026, telah disetujui Pembentukan Balai Teknologi Industri Manufaktur (BTIM) Oleh Kemenpan RB.</p><p>&nbsp;</p>',
            ],
        ]);

        // Tentang IMC > Tugas dan Fungsi
        $this->seedSubMenuAdditionalTexts($datetime, 'tugas-dan-fungsi', [
            [
                'title' => 'Tugas',
                'order' => 1,
                'description' => '<p>Melaksanakan layanan teknologi manufaktur untuk meningkatkan daya saing industri nasional.</p>',
            ],
            [
                'title' => 'Fungsi',
                'order' => 2,
                'description' => '<ol><li data-list-item-id="e5a0293fe2074d7c15209118929849836">pelaksanaan layanan pengembangan produk dan penerapan teknologi industri seperti rekayasa desain produk, pembuatan purwarupa, pengujian performa produk sebelum menjadi produk komersial;&nbsp;</li><li data-list-item-id="e18ca3f08c04f8a67f875a6d596e83763">pelaksanaan pendampingan transfer teknologi dan hilirisasi hasil riset;&nbsp;</li><li data-list-item-id="e50381f382b256cb93a4a4c7a1926cd20">pengembangan kompetensi sumber daya manusia industri di bidang teknologi manufaktur;&nbsp;</li><li data-list-item-id="eda01fe4355b8a135303746bc6bbdb6b9">pendampingan penerapan standar teknologi manufaktur;&nbsp;</li><li data-list-item-id="ef41289450eac9274a6ab43be5d6d8724">pelaksanaan fasilitasi jejaring dan kerja sama dengan pihak terkait di bidang teknologi manufaktur;&nbsp;</li><li data-list-item-id="edd4f637a8794b10767cfa867780723b4">pelaksanaan adopsi teknologi untuk mendukung penguatan daya saing industri manufaktur nasional;&nbsp;</li><li data-list-item-id="e00d020b9939325c9107a2f3c79d083c1">pelaksanaan urusan perencanaan, program, anggaran, sumber daya manusia, keuangan, organisasi, tata laksana, kerja sama, hubungan masyarakat, data dan informasi, pengelolaan barang milik/kekayaan negara, persuratan, perpustakaan, kearsipan, dan rumah tangga; dan&nbsp;</li><li data-list-item-id="e6a9890f29b0a6637f5c0a3c4abbab88b">pelaksanaan evaluasi dan pelaporan.</li></ol>',
            ],
        ]);

        // Tentang IMC > Visi dan Misi
        $this->seedSubMenuAdditionalTexts($datetime, 'visi-dan-misi', [
            [
                'title' => 'Visi IMC',
                'order' => 1,
                'description' => '<p><strong>"Menjadi pusat unggulan pengembangan produk dan teknologi manufaktur yang mendorong kemandirian industri nasional dan daya saing global."</strong></p>',
            ],
            [
                'title' => 'Misi IMC',
                'order' => 2,
                'description' => '<ol><li><strong>Mendorong Pengembangan Produk dan Transfer Teknologi</strong><br>Mengembangkan dan mengakselerasi pengembangan produk serta proses industri melalui kolaborasi antara pelaku industri, akademisi, dan lembaga riset.</li><li><strong>Meningkatkan Kompetensi dan Ketersediaan Talenta Industri</strong><br>Menyediakan program pelatihan, inkubasi, dan pengembangan technopreneurship untuk menghasilkan SDM industri yang unggul, adaptif, dan siap menghadapi transformasi digital.</li><li><strong>Memfasilitasi Pengembangan Teknologi Manufaktur Nasional</strong><br>Menyediakan fasilitas modern dan layanan teknis untuk riset terapan, rekayasa desain, prototipe, dan uji coba teknologi manufaktur.</li><li><strong>Membangun Proyek Kolaborasi Pengembangan Produk dan Teknologi</strong><br>Menjalankan proyek bersama mitra strategis industri, startup, dan lembaga riset dalam pengembangan produk dan teknologi manufaktur yang aplikatif dan berdaya saing.</li><li><strong>Memperkuat Jejaring Kerjasama dan Ekosistem Industri</strong><br>Membangun kemitraan strategis dengan industri, IKM, startup, asosiasi, dan pemangku kepentingan lainnya guna memperluas akses pasar, investasi teknologi, dan kolaborasi inovatif.</li><li><strong>Mendukung Akselerasi Transformasi Industri 4.0</strong><br>Menjadi penggerak transformasi digital di sektor manufaktur melalui pengembangan teknologi proses, efisiensi sistem produksi, dan integrasi sistem berbasis ICT.</li></ol>',
            ],
        ]);

        // Tentang IMC > Sejarah IMC
        $this->seedSubMenuAdditionalTexts($datetime, 'sejarah-imc', [
            [
                'title' => 'Sejarah IMC',
                'order' => 1,
                'description' => '<p>&nbsp;</p><p>Dalam era persaingan global yang semakin ketat, industri manufaktur nasional dihadapkan pada tantangan besar dalam proses pengembangan produk. Tahapan inovasi teknologi dan desain manufaktur saat ini memerlukan waktu yang panjang, biaya yang tinggi, serta risiko yang besar, terutama bagi pelaku industri manufatur. Tanpa adanya dukungan kelembagaan yang terstruktur dan memadai, upaya pengembangan produk akan menjadi tidak efisien dan cenderung bergantung pada impor. Oleh karena itu, <strong>kehadiran Unit Pelaksana Teknis (UPT) Indonesia Manufacturing Center (IMC)</strong>menjadi sangat penting dan mendesak sebagai fasilitator utama dalam proses rekayasa, validasi teknologi, dan akselerasi hilirisasi produk manufaktur.</p><p>Urgensi pembentukan UPT IMC tidak hanya untuk menjawab tantangan teknis tersebut, namun juga untuk mendorong <strong>transformasi struktur industri nasional menuju kemandirian dan daya saing global</strong>. IMC dirancang untuk meningkatkan <strong>Tingkat Komponen Dalam Negeri (TKDN)</strong>melalui pendampingan teknis dan penyediaan layanan pengembangan produk substitusi impor. Hal ini berkontribusi langsung terhadap pengurangan ketergantungan pada barang-barang impor yang selama ini menjadi kelemahan fundamental sektor industri kita.</p><p>Lebih lanjut, IMC berperan sebagai <strong>One Stop Service, </strong>dengan fokus dan keberpihakan yang lebih kuat pada kebutuhan riil industri. Selama ini, ide-ide inovasi dari perguruan tinggi sering kali belum sejalan (tidak <i>match</i>) dengan kebutuhan pasar atau proses manufaktur aktual. Sebaliknya, inovasi yang berasal dari dunia industri umumnya bersifat lebih aplikatif, kontekstual, dan langsung menyasar peningkatan efisiensi serta daya saing produk. Oleh karena itu, IMC memfasilitasi agar <strong>inovasi yang muncul dari sektor industri dapat lebih cepat dikembangkan dan dialihteknologikan (technology transfer)</strong>menjadi produk yang siap dikomersialisasikan. Ini menjadikan IMC sebagai instrumen strategis dalam menjembatani kebutuhan industri dan kapabilitas teknologi nasional.</p><p>Selain aspek teknologi, IMC juga berkontribusi besar terhadap <strong>pengembangan sumber daya manusia (SDM)</strong>industri melalui pelatihan teknis, inkubasi technopreneur, serta kolaborasi dengan lembaga pendidikan vokasi dan politeknik. IMC tidak hanya bertugas meningkatkan kompetensi individu, tetapi juga menciptakan pelaku industri baru yang mampu berinovasi dan menghasilkan solusi bagi tantangan nyata sektor manufaktur.</p><p>Akhirnya, untuk memastikan seluruh fungsi tersebut dapat dijalankan secara profesional, berkelanjutan, dan terintegrasi, diperlukan kelembagaan formal berupa UPT IMC. Tanpa bentuk kelembagaan yang jelas, sulit bagi IMC untuk mengelola sumber daya, menjalin kemitraan, serta memberikan layanan kepada industri secara optimal. Pembentukan UPT IMC adalah langkah konkret dalam membangun <strong>rantai pasok nasional berbasis teknologi, memperkuat ekosistem inovasi, dan menciptakan industri manufaktur Indonesia yang berdaya saing dan mandiri.</strong></p><p>Pembangunan Indonesia Manufacturing Center (IMC) telah mengalami beberapa tahap penting. Berikut adalah progres pembangunan IMC:</p><ol><li><strong>Groundbreaking</strong>: Pembangunan IMC dimulai dengan groundbreaking pada tanggal 5 Desember 2022. Acara ini dihadiri oleh berbagai pejabat, termasuk Menteri Perindustrian Agus Gumiwang Kartasmita.</li><li><strong>Topping Off Ceremony</strong>: Pada tanggal 19 September 2023, IMC mencapai tahap akhir konstruksi yang disebut <i>topping off</i>, di mana atap gedung dipasang. Progres keseluruhan pada saat itu mencapai 38%.</li><li><strong>Penyelesaian</strong>: Pembangunan IMC dinyatakan selesai 100% pada tanggal 16 Agustus 2024. Bangunan ini terdiri dari gedung utama 6 lantai dan fasilitas workshop/teaching factory di tiga lantai, serta dilengkapi dengan asrama, masjid, ruang utilitas, dan fasilitas lainnya.</li><li><strong>Peresmian</strong>: IMC diresmikan pada tanggal 14 Oktober 2024 oleh Menteri Perindustrian RI, Agus Gumiwang Kartasmita. Gedung IMC terletak di Citeko, Kecamatan Plered, Kabupaten Purwakarta, Jawa Barat.</li><li><strong>Operasional</strong>: Tahun 2024 IMC melakukan layanan Pelatihan / Bimbingan Teknis kepada stakeholder sesuai kebutuhan industri manufaktur.</li><li><strong>Kelembagaan </strong>: Tahun 2025 Kemenperin mengajukan proses pembentukan kelembagaan UPT &nbsp;kepada Kementerian PAN dan RB. Tanggal 2 Januari 2026 Kemenpan RB memberikan persetujuan pembentukan kelembagaan IMC dengan nomenklatur UPT Balai Teknologi Industri Manufaktur (BTIM)</li></ol><p>Foto-foto :</p><p><img src="http://imc-api.kemenperin.go.id/media/uploads/b3902363f7f64e59bceaf94782cef70b.jpeg"></p><p>Groundbreaking pada tanggal 5 Desember 2022.</p><p>&nbsp;</p><p><img src="http://imc-api.kemenperin.go.id/media/uploads/60f16c8f7427413c996f418f0aafb414.jpeg"></p><p>Topping Off Ceremony &nbsp;Pada tanggal 19 September 2023</p><p><img src="http://imc-api.kemenperin.go.id/media/uploads/1dc1b08aac764c08935504adbebbe104.jpeg"></p><p>IMC diresmikan pada tanggal 14 Oktober 2024</p><p>&nbsp;</p><p>&nbsp;</p>',
            ],
        ]);

        // Tentang IMC > Layanan Kami
        $this->seedSubMenuAdditionalTexts($datetime, 'layanan-kami', [
            [
                'title' => 'Layanan Kami',
                'order' => 1,
                'description' => '<p>&nbsp;</p><p>Indonesia Manufacturing Center (IMC) merupakan pusat layanan pengembangan produk dan teknologi manufaktur yang terintegrasi dari hulu ke hilir. IMC menyediakan berbagai layanan mulai dari intake usulan proyek, desain produk dan proses manufaktur, pembuatan prototipe, pengujian kualitas, hingga promosi dan komersialisasi produk. Layanan ini dirancang untuk mendukung pelaku industri, khususnya IKM, dalam mengakselerasi inovasi dan meningkatkan daya saing.</p><h3><strong>Layanan IMC (Indonesia Manufacturing Center)</strong></h3><h4>1. <strong>Desain Produk dan Prototyping</strong></h4><ul><li>Jasa rekayasa desain produk (CAD, CAE, CAM).</li><li>Pembuatan prototipe cepat menggunakan 3D printing dan teknologi manufaktur aditif.</li><li>Optimasi desain untuk produksi massal dan efisiensi biaya.</li></ul><h4>2. <strong>Pengujian dan Validasi Produk</strong></h4><ul><li>Fasilitas uji mekanik, uji ketahanan, dan simulasi performa produk.</li><li>Pengujian berbasis perangkat lunak (misalnya ANSYS CFD).</li><li>Sertifikasi mutu awal untuk mendukung kelayakan pasar.</li></ul><h4>3. <strong>Transfer Teknologi dan Komersialisasi Inovasi</strong></h4><ul><li>Konsultasi teknis dan bisnis untuk adopsi teknologi.</li><li>Fasilitasi lisensi dan transfer hak kekayaan intelektual (HKI).</li><li>Pendampingan hilirisasi hasil riset ke industri.</li></ul><h4>4. <strong>Inkubasi Bisnis dan Technopreneurship</strong></h4><ul><li>Program akselerasi startup berbasis manufaktur.</li><li>Pendampingan model bisnis dan strategi go-to-market.</li><li>Akses ke investor, mitra industri, dan fasilitas produksi.</li></ul><h4>5. <strong>Pelatihan dan Pengembangan Talenta Industri</strong></h4><ul><li>Pelatihan teknis berbasis kebutuhan industri (hard skills dan soft skills).</li><li>Sertifikasi kompetensi dan penguatan SDM manufaktur.</li><li>Program magang terintegrasi untuk mahasiswa dan tenaga vokasi.</li></ul><h4>6. <strong>Konsultasi Teknologi dan Transformasi Digital</strong></h4><ul><li>Pendampingan transformasi menuju Industri 4.0 (IoT, automasi, digital twin).</li><li>Audit teknologi dan efisiensi proses produksi.</li><li>Pemetaan kesiapan teknologi industri (technology readiness level).</li></ul><h4>7. <strong>Pengembangan Produk Kolaboratif</strong></h4><ul><li>Platform kolaborasi multi-stakeholder untuk co-development produk inovatif.</li><li>Proyek bersama dengan mitra industri, startup, dan perguruan tinggi.</li><li>Fokus pada substitusi impor, efisiensi energi, dan teknologi tepat guna.</li></ul><h4>8. <strong>Jejaring Kerja Sama dan Akses Pasar</strong></h4><ul><li>Hub industri dan matchmaking dengan buyer, vendor, dan mitra teknologi.</li><li>Promosi produk inovasi melalui expo, showcase, dan demo day.</li><li>Pengembangan ekosistem industri melalui forum, FGD, dan klasterisasi.</li></ul><h4>9. <strong>Akses Fasilitas Manufaktur dan Infrastruktur Modern</strong></h4><ul><li>Penggunaan laboratorium, mesin CNC, 3D scanner, fasilitas desain, dan uji coba.</li><li>Layanan berbasis sewa fasilitas untuk startup dan IKM yang belum memiliki peralatan.</li><li>Dukungan teknis dari tenaga ahli IMC dan mitra industri.</li></ul>',
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