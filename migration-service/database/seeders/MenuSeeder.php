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

        $hasilId = $this->createMenu([
            'title' => 'Hasil Kolaborasi',
            'slug' => 'hasil-kolaborasi',
            'is_active' => 1,
            'is_dedicated' => 1,
            'has_sub' => false,
            'order' => 4,
            'is_show_front' => true,
        ]);

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
        $this->createContentRecord($hasilId, 'project');
        $this->createContentRecord($showcaseId, 'gallery');
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

    private function createSubMenuContentRecord(int $subMenuId, string $type): void
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
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ]);
    }

    private function createTentangImcSubMenus(int $tentangImcId): void
    {
        $subMenus = [
            ['title' => 'Profil IMC', 'slug' => 'profil-imc', 'order' => 1, 'type' => 'text'],
            ['title' => 'Tugas dan Fungsi', 'slug' => 'tugas-dan-fungsi', 'order' => 2, 'type' => 'text'],
            ['title' => 'Visi dan Misi', 'slug' => 'visi-dan-misi', 'order' => 3, 'type' => 'text'],
            ['title' => 'Sejarah IMC', 'slug' => 'sejarah-imc', 'order' => 4, 'type' => 'text'],
            ['title' => 'Struktur Organisasi', 'slug' => 'struktur-organisasi', 'order' => 5, 'type' => 'organizational_structure'],
            ['title' => 'Layanan Kami', 'slug' => 'layanan-kami', 'order' => 6, 'type' => 'text'],
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

            $this->createSubMenuContentRecord($subMenuId, $subMenuData['type']);
        }
    }

    private function createBerandaSections(int $berandaId): void
    {
        $sections = [
            ['title' => 'Slider', 'type' => 'image_carousel'],
            ['title' => 'Proyek Kolaborasi', 'type' => 'consume'],
            ['title' => 'Showcase', 'type' => 'consume'],
            ['title' => 'Ekosistem', 'type' => 'image_link'],
            ['title' => 'Video', 'type' => 'video_carousel'],
            ['title' => 'Card Link', 'type' => 'card_link'],
            ['title' => 'Accordion', 'type' => 'accordion'],
            ['title' => 'Repository', 'type' => 'repository'],
            ['title' => 'Organizational Structure', 'type' => 'organizational_structure'],
        ];

        foreach ($sections as $key => $section) {
            DB::table('contents')->insert([
                'parent_type' => 'App\Models\Menu',
                'parent_id' => $berandaId,
                'section_title' => $section['title'],
                'type' => $section['type'],
                'order' => $key + 1,
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
