<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    private string $datetime;

    public function run(): void
    {
        $this->datetime = date('Y-m-d H:i:s');

        // --- Menus ---
        $berandaId = $this->createMenu([
            'title' => 'Beranda',
            'slug' => 'beranda',
            'is_active' => 1,
            'is_dedicated' => 1,
            'has_sub' => false,
            'order' => 1,
            'is_show_front' => true,
        ]);

        $tentangImcId = $this->createMenu([
            'title' => 'Tentang IMC',
            'slug' => 'tentang-imc',
            'is_active' => 1,
            'is_dedicated' => 1,
            'has_sub' => true,
            'order' => 2,
            'is_show_front' => true,
            'is_show_side_menu' => true,
        ]);

        $proyekId = $this->createMenu([
            'title' => 'Proyek Kolaborasi',
            'slug' => 'proyek-kolaborasi',
            'is_active' => 1,
            'is_dedicated' => 1,
            'has_sub' => false,
            'order' => 3,
            'is_show_front' => true,
        ]);

        // $hasilId = $this->createMenu([
        //     'title' => 'Hasil Kolaborasi',
        //     'slug' => 'hasil-kolaborasi',
        //     'is_active' => 1,
        //     'is_dedicated' => 1,
        //     'has_sub' => false,
        //     'order' => 4,
        //     'is_show_front' => true,
        // ]);

        $showcaseId = $this->createMenu([
            'title' => 'Showcase',
            'slug' => 'showcase',
            'is_active' => 1,
            'is_dedicated' => 1,
            'has_sub' => false,
            'order' => 5,
            'is_show_front' => true,
        ]);

        $kegiatanId = $this->createMenu([
            'title' => 'Kegiatan',
            'slug' => 'kegiatan',
            'is_active' => 1,
            'is_dedicated' => 1,
            'has_sub' => false,
            'order' => 6,
            'is_show_front' => true,
        ]);

        $beritaId = $this->createMenu([
            'title' => 'Berita dan Artikel',
            'slug' => 'berita-dan-artikel',
            'is_active' => 1,
            'is_dedicated' => 1,
            'has_sub' => false,
            'order' => 7,
            'is_show_front' => true,
        ]);

        // --- Content records for non-Beranda, non-TentangIMC menus ---
        $this->createContentRecord($proyekId, 'project');
        // $this->createContentRecord($hasilId, 'project'); takeout karena sama dgn showcase
        $this->createContentRecord($showcaseId, 'showcase');
        $this->createContentRecord($kegiatanId, 'agenda');
        $this->createContentRecord($beritaId, 'publication');

        // --- Tentang IMC sub-menus ---
        $this->createTentangImcSubMenus($tentangImcId);

        // --- Beranda sections ---
        $this->createBerandaSections($berandaId);
    }

    private function createMenu(array $data): int
    {
        $data['created_at'] = $this->datetime;
        $data['updated_at'] = $this->datetime;

        return DB::table('menus')->insertGetId($data);
    }

    private function createSubMenu(array $data): int
    {
        $data['created_at'] = $this->datetime;
        $data['updated_at'] = $this->datetime;

        return DB::table('sub_menus')->insertGetId($data);
    }

    private function createContentRecord(int $menuId, string $type): void
    {
        $layout = DB::table('ms_layout')->where('type', $type)->first();

        DB::table('contents')->insert([
            'parent_type' => 'App\Models\Menu',
            'parent_id' => $menuId,
            'type' => $type,
            'layout_id' => $layout?->id,
            'is_section' => false,
            'is_show_home' => true,
            'is_show_tag' => true,
            'is_accessible' => true,
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ]);
    }

    private function createSubMenuContentRecord(int $subMenuId, string $type, array $options = []): void
    {
        $layout = DB::table('ms_layout')->where('type', $type)->first();

        DB::table('contents')->insert([
            'parent_type' => 'App\Models\SubMenu',
            'parent_id' => $subMenuId,
            'type' => $type,
            'layout_id' => $layout?->id,
            'is_section' => false,
            'is_show_home' => false,
            'is_show_tag' => true,
            'is_accessible' => true,
            'has_additional_text' => $options['has_additional_text'] ?? false,
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ]);
    }

    private function createTentangImcSubMenus(int $tentangImcId): void
    {
        $subMenus = [
            ['title' => 'Profil IMC', 'slug' => 'profil-imc', 'order' => 1, 'type' => 'image_carousel', 'has_additional_text' => true],
            ['title' => 'Tugas dan Fungsi', 'slug' => 'tugas-dan-fungsi', 'order' => 2, 'type' => 'image_carousel', 'has_additional_text' => true],
            ['title' => 'Visi dan Misi', 'slug' => 'visi-dan-misi', 'order' => 3, 'type' => 'image_carousel', 'has_additional_text' => true],
            ['title' => 'Sejarah IMC', 'slug' => 'sejarah-imc', 'order' => 4, 'type' => 'image_carousel', 'has_additional_text' => true],
            ['title' => 'Struktur Organisasi', 'slug' => 'struktur-organisasi', 'order' => 5, 'type' => 'organizational_structure'],
            ['title' => 'Layanan Kami', 'slug' => 'layanan-kami', 'order' => 6, 'type' => 'image_carousel', 'has_additional_text' => true],
            ['title' => 'Kontak Kami', 'slug' => 'kontak-kami', 'order' => 7, 'type' => 'text'],
        ];

        foreach ($subMenus as $subMenuData) {
            $subMenuId = $this->createSubMenu([
                'menu_id' => $tentangImcId,
                'title' => $subMenuData['title'],
                'slug' => $subMenuData['slug'],
                'order' => $subMenuData['order'],
                'has_child' => false,
                'is_active' => true,
                'is_show_side_menu' => true,
            ]);

            $this->createSubMenuContentRecord($subMenuId, $subMenuData['type'], $subMenuData);
        }
    }

    private function createBerandaSections(int $berandaId): void
    {
        $sections = [
            ['title' => 'Hero', 'type' => 'hero_carousel', 'description'=> '', 'order' => 1],
            ['title' => 'Layanan Unggulan', 'type' => 'image_link', 'description'=> 'Solusi terpadu dari pengembangan konsep hingga produksi massal yang siap bersaing di pasar.', 'order' => 2],
            ['title' => 'Proyek Kolaborasi & Manufaktur', 'type' => 'consume', 'description'=> 'Sinergi riset, rekayasa industri, dan pengembangan komponen utama mesin manufaktur yang diproduksi oleh talenta nasional.', 'order' => 3],
            ['title' => 'Showcase Teknologi & Hasil Karya Manufaktur', 'type' => 'consume', 'description'=> 'Eksplorasi prototipe, mesin hasil rekkayasa balik (reverse engineering), dan proyek kolaborasi teknologi karya anak bangsa binaan Indonesia Manufacturing Center (IMC)', 'order' => 4],
            ['title' => 'Bagaimana Ekosistem IMC Bekerja untuk Industri?', 'type' => 'image_link', 'description'=> 'Indonesia Manufacturing Center (IMC) adalah jembatan akselerasi teknologi industri yang mendukung percepatan kemandirian mesin dan alat rekayasa manufaktur nasional melalui kolaborasi terstruktur akademisi, riset asosiasi, dan pelaku industri.', 'order' => 5],
            ['title' => 'Mitra', 'type' => 'image_link', 'description'=> '', 'order' => 6],
        ];

        foreach ($sections as $key => $section) {
            DB::table('contents')->insert([
                'parent_type' => 'App\Models\Menu',
                'parent_id' => $berandaId,
                'section_title' => $section['title'],
                'type' => $section['type'],
                'order' => $section['order'],
                'section_description' => $section['description'],
                'is_section' => true,
                'is_show_home' => true,
                'is_show_tag' => false,
                'is_accessible' => true,
                'created_at' => $this->datetime,
                'updated_at' => $this->datetime,
            ]);
        }
    }
}
