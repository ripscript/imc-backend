<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailTextSeeder extends Seeder
{
    public function run(): void
    {
        $datetime = date('Y-m-d H:i:s');

        // Text contents for Tentang IMC sub-menus
        $subMenuTexts = [
            ['slug' => 'kontak-kami', 'content' => '<p>Indonesia Manufacturing Center (IMC) selalu terbuka untuk menerima pertanyaan, masukan, saran, maupun pengaduan dari masyarakat dan pelaku industri. Kami berkomitmen memberikan layanan terbaik dan respons cepat terhadap setiap kebutuhan Anda. &nbsp;</p><p><a href="http://eepurl.com/jqX4WU"><img src="http://imc-api.kemenperin.go.id/media/uploads/9f1550edfe6f4b7d95a30acc2ea3c120.jpeg"></a></p><p>Dapatkan informasi terkini mengenai kegiatan, layanan, dan program Indonesia Manufacturing Center (IMC) dengan berlangganan melalui email Anda.<br><a href="http://eepurl.com/jqX4WU"><strong>Masukkan alamat email</strong></a><strong></strong>Anda di bawah ini untuk menerima pembaruan resmi dari IMC.</p><p>Anda dapat menghubungi kami melalui:</p><p><strong>A. Alamat IMC&nbsp;</strong></p><p>Jln. Raya Warung Jeruk, Citeko, Kec. Plered, Kab. Purwakarta</p><p>Jawa Barat 41165</p><p><strong>B. Layanan Chatbot IMC</strong></p><p>ndonesia Manufacturing Center (IMC) menyediakan <strong>Chatbot IMC</strong>sebagai kanal komunikasi interaktif bagi masyarakat umum, pelaku industri, akademisi, perguruan tinggi, maupun pelaku IKM.</p><p>Chatbot IMC dirancang untuk memberikan <strong>jawaban cepat, akurat, dan praktis</strong>kapan pun Anda membutuhkannya, tanpa harus menunggu jam kerja.</p><p>Berikut link <a href="https://www.chatbase.co/chatbot-iframe/0H9mOeVQJBOU3g6SrcH_z"><strong>chatbot IMC</strong></a></p><p><strong>C. Layanan Umum</strong></p><p>Masyarakat dapat memberikan masukan, saran, atau menanyakan informasi umum terkait layanan Indonesia Manufacturing Center (IMC) &nbsp;melalui email resmi kami di: <strong>layanan-imc@kemenperin.go.id</strong></p><p><strong>D. Pengaduan Masyarakat SP4N-LAPOR!</strong></p><p><strong>Link Nasional:</strong><a href="https://www.lapor.go.id/">https://www.lapor.go.id/</a></p><p>Pengelolaan pengaduan pelayanan publik di Indonesia selama ini belum terintegrasi dengan baik. Banyak organisasi penyelenggara mengelola pengaduan secara parsial, sehingga sering terjadi duplikasi penanganan, atau bahkan pengaduan tidak tertangani karena dianggap bukan kewenangannya.</p><p>Untuk mendukung terwujudnya tata kelola pemerintahan yang baik (<i>good governance</i>), sistem pengelolaan pengaduan publik kini diintegrasikan melalui <strong>SP4N-LAPOR!</strong>. Sistem ini menghadirkan satu pintu pengaduan nasional, sehingga masyarakat memiliki saluran resmi yang terpusat dan terkoordinasi.</p><p><strong>Tentang SP4N-LAPOR!</strong></p><p>SP4N-LAPOR! dibentuk untuk merealisasikan kebijakan <strong>"No Wrong Door Policy"</strong>, yang menjamin setiap pengaduan dari masyarakat akan diteruskan ke instansi berwenang, tanpa terkendala urusan kewenangan.</p><p>Tujuan SP4N-LAPOR! antara lain:</p><p>Memudahkan penyelenggara dalam mengelola pengaduan secara sederhana, cepat, tepat, tuntas, dan terkoordinasi.</p><p>Memberikan akses seluas-luasnya bagi masyarakat untuk berpartisipasi dalam menyampaikan pengaduan.</p><p>Meningkatkan kualitas pelayanan publik di seluruh Indonesia.</p><p>Saat ini, SP4N-LAPOR! telah terhubung dengan <strong>34 Kementerian, 96 Lembaga, dan 493 Pemerintah Daerah di Indonesia.</strong></p><p><strong>Fitur Utama SP4N-LAPOR!</strong></p><p><strong>Anonim:</strong>Pelapor dapat merahasiakan identitas dari pihak terlapor maupun publik.</p><p><strong>Rahasia:</strong>Isi laporan hanya dapat diakses pihak berwenang.</p><p><strong>Tracking ID:</strong>Nomor unik untuk memantau status tindak lanjut laporan.</p><p><strong>"Berani LAPOR! Untuk Pelayanan Publik yang Lebih Baik."</strong></p>'],
        ];

        foreach ($subMenuTexts as $item) {
            $subMenu = DB::table('sub_menus')->where('slug', $item['slug'])->first();
            if (!$subMenu) {
                continue;
            }

            $contentRecord = DB::table('contents')
                ->where('parent_type', 'App\Models\SubMenu')
                ->where('parent_id', $subMenu->id)
                ->where('type', 'text')
                ->first();

            if (!$contentRecord) {
                continue;
            }

            $existing = DB::table('content_detail_texts')->where('content_id', $contentRecord->id)->first();

            if (!$existing) {
                DB::table('content_detail_texts')->insert([
                    'content_id' => $contentRecord->id,
                    'content' => $item['content'],
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
            }
        }
    }
}
