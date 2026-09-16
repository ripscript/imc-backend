<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentDetailImageCarouselSeeder extends Seeder
{
    public function run(): void
    {
        $datetime = date('Y-m-d H:i:s');

        // Beranda hero slider (section content, is_section=true)
        $this->seedBerandaSlider($datetime);

        // Tentang IMC > Profil IMC (sub-menu page content)
        $this->seedSubMenuCarousel($datetime, 'profil-imc', [
            ['title' => 'Kawasan IMC', 'image_path' => '/images/profil-imc-1.jpg'],
            ['title' => 'Fasilitas IMC', 'image_path' => '/images/profil-imc-2.jpg'],
            ['title' => 'Kegiatan IMC', 'image_path' => '/images/profil-imc-3.jpg'],
        ]);

        // Tentang IMC > Tugas dan Fungsi
        $this->seedSubMenuCarousel($datetime, 'tugas-dan-fungsi', [
            ['title' => 'Tugas dan Fungsi 1', 'image_path' => '/images/profil-imc-1.jpg'],
            ['title' => 'Tugas dan Fungsi 2', 'image_path' => '/images/profil-imc-2.jpg'],
            ['title' => 'Tugas dan Fungsi 3', 'image_path' => '/images/profil-imc-3.jpg'],
        ]);

        // Tentang IMC > Visi dan Misi
        $this->seedSubMenuCarousel($datetime, 'visi-dan-misi', [
            ['title' => 'Visi dan Misi 1', 'image_path' => '/images/profil-imc-1.jpg'],
            ['title' => 'Visi dan Misi 2', 'image_path' => '/images/profil-imc-2.jpg'],
            ['title' => 'Visi dan Misi 3', 'image_path' => '/images/profil-imc-3.jpg'],
        ]);

        // Tentang IMC > Sejarah IMC
        $this->seedSubMenuCarousel($datetime, 'sejarah-imc', [
            ['title' => 'Sejarah IMC 1', 'image_path' => '/images/profil-imc-1.jpg'],
            ['title' => 'Sejarah IMC 2', 'image_path' => '/images/profil-imc-2.jpg'],
            ['title' => 'Sejarah IMC 3', 'image_path' => '/images/profil-imc-3.jpg'],
        ]);

        // Tentang IMC > Layanan Kami
        $this->seedSubMenuCarousel($datetime, 'layanan-kami', [
            ['title' => 'Layanan Kami 1', 'image_path' => '/images/profil-imc-1.jpg'],
            ['title' => 'Layanan Kami 2', 'image_path' => '/images/profil-imc-2.jpg'],
            ['title' => 'Layanan Kami 3', 'image_path' => '/images/profil-imc-3.jpg'],
        ]);
    }

    private function seedBerandaSlider(string $datetime): void
    {
        $content = DB::table('contents')
            ->where('type', 'image_carousel')
            ->where('is_section', true)
            ->first();

        if (! $content) {
            return;
        }

        for ($i = 1; $i <= 5; $i++) {
            DB::table('content_detail_image_carousels')->insert([
                'content_id' => $content->id,
                'image_path' => "/images/slider-{$i}.jpg",
                'title' => "Slider IMC {$i}",
                'description' => "Deskripsi slider ke-{$i}",
                'link' => '/slider-' . $i,
                'order' => $i,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }

    private function seedSubMenuCarousel(string $datetime, string $subMenuSlug, array $slides): void
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

        $alreadySeeded = DB::table('content_detail_image_carousels')
            ->where('content_id', $content->id)
            ->exists();

        if ($alreadySeeded) {
            return;
        }

        foreach ($slides as $key => $slide) {
            DB::table('content_detail_image_carousels')->insert([
                'content_id' => $content->id,
                'image_path' => $slide['image_path'],
                'title' => $slide['title'],
                'description' => null,
                'link' => null,
                'order' => $key + 1,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }
}
