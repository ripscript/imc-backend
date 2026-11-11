<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'dashboard', 'action' => 'read', 'label' => 'Lihat Dashboard', 'group' => 'Dashboard'],

            // User Management
            ['name' => 'user', 'action' => 'create', 'label' => 'Tambah Pengguna', 'group' => 'Manajemen Pengguna'],
            ['name' => 'user', 'action' => 'read', 'label' => 'Lihat Pengguna', 'group' => 'Manajemen Pengguna'],
            ['name' => 'user', 'action' => 'update', 'label' => 'Ubah Pengguna', 'group' => 'Manajemen Pengguna'],
            ['name' => 'user', 'action' => 'delete', 'label' => 'Hapus Pengguna', 'group' => 'Manajemen Pengguna'],

            // User Profile
            ['name' => 'profile', 'action' => 'read', 'label' => 'Lihat Profil', 'group' => 'Profil Pengguna'],

            // Role Management
            ['name' => 'role', 'action' => 'create', 'label' => 'Tambah Peran', 'group' => 'Manajemen Peran & Akses'],
            ['name' => 'role', 'action' => 'read', 'label' => 'Lihat Peran', 'group' => 'Manajemen Peran & Akses'],
            ['name' => 'role', 'action' => 'update', 'label' => 'Ubah Peran', 'group' => 'Manajemen Peran & Akses'],
            ['name' => 'role', 'action' => 'delete', 'label' => 'Hapus Peran', 'group' => 'Manajemen Peran & Akses'],

            // Permission Management
            ['name' => 'permission', 'action' => 'create', 'label' => 'Tambah Hak Akses', 'group' => 'Manajemen Peran & Akses'],
            ['name' => 'permission', 'action' => 'read', 'label' => 'Lihat Hak Akses', 'group' => 'Manajemen Peran & Akses'],
            ['name' => 'permission', 'action' => 'update', 'label' => 'Ubah Hak Akses', 'group' => 'Manajemen Peran & Akses'],
            ['name' => 'permission', 'action' => 'delete', 'label' => 'Hapus Hak Akses', 'group' => 'Manajemen Peran & Akses'],

            // Project Management
            ['name' => 'project', 'action' => 'create', 'label' => 'Tambah Proyek', 'group' => 'Manajemen Proyek'],
            ['name' => 'project', 'action' => 'read', 'label' => 'Lihat Proyek', 'group' => 'Manajemen Proyek'],
            ['name' => 'project', 'action' => 'update', 'label' => 'Ubah Proyek', 'group' => 'Manajemen Proyek'],
            ['name' => 'project', 'action' => 'delete', 'label' => 'Hapus Proyek', 'group' => 'Manajemen Proyek'],
            ['name' => 'project', 'action' => 'approve', 'label' => 'Persetujuan Proyek', 'group' => 'Manajemen Proyek'],

            // Approval Management
            ['name' => 'approval', 'action' => 'read', 'label' => 'Lihat Persetujuan', 'group' => 'Manajemen Proyek'],
            ['name' => 'approval', 'action' => 'approve', 'label' => 'Setujui Persetujuan', 'group' => 'Manajemen Proyek'],
            ['name' => 'approval', 'action' => 'reject', 'label' => 'Tolak Persetujuan', 'group' => 'Manajemen Proyek'],

            // Master Proyek
            ['name' => 'client', 'action' => 'create', 'label' => 'Tambah Klien', 'group' => 'Master Proyek'],
            ['name' => 'client', 'action' => 'read', 'label' => 'Lihat Klien', 'group' => 'Master Proyek'],
            ['name' => 'client', 'action' => 'update', 'label' => 'Ubah Klien', 'group' => 'Master Proyek'],
            ['name' => 'client', 'action' => 'delete', 'label' => 'Hapus Klien', 'group' => 'Master Proyek'],

            ['name' => 'partner', 'action' => 'create', 'label' => 'Tambah Mitra', 'group' => 'Master Proyek'],
            ['name' => 'partner', 'action' => 'read', 'label' => 'Lihat Mitra', 'group' => 'Master Proyek'],
            ['name' => 'partner', 'action' => 'update', 'label' => 'Ubah Mitra', 'group' => 'Master Proyek'],
            ['name' => 'partner', 'action' => 'delete', 'label' => 'Hapus Mitra', 'group' => 'Master Proyek'],

            ['name' => 'project_category', 'action' => 'create', 'label' => 'Tambah Kategori Proyek', 'group' => 'Master Proyek'],
            ['name' => 'project_category', 'action' => 'read', 'label' => 'Lihat Kategori Proyek', 'group' => 'Master Proyek'],
            ['name' => 'project_category', 'action' => 'update', 'label' => 'Ubah Kategori Proyek', 'group' => 'Master Proyek'],
            ['name' => 'project_category', 'action' => 'delete', 'label' => 'Hapus Kategori Proyek', 'group' => 'Master Proyek'],

            ['name' => 'document_type', 'action' => 'create', 'label' => 'Tambah Tipe Dokumen', 'group' => 'Master Proyek'],
            ['name' => 'document_type', 'action' => 'read', 'label' => 'Lihat Tipe Dokumen', 'group' => 'Master Proyek'],
            ['name' => 'document_type', 'action' => 'update', 'label' => 'Ubah Tipe Dokumen', 'group' => 'Master Proyek'],
            ['name' => 'document_type', 'action' => 'delete', 'label' => 'Hapus Tipe Dokumen', 'group' => 'Master Proyek'],

            ['name' => 'workflow', 'action' => 'create', 'label' => 'Tambah Alur Kerja', 'group' => 'Master Proyek'],
            ['name' => 'workflow', 'action' => 'read', 'label' => 'Lihat Alur Kerja', 'group' => 'Master Proyek'],
            ['name' => 'workflow', 'action' => 'update', 'label' => 'Ubah Alur Kerja', 'group' => 'Master Proyek'],
            ['name' => 'workflow', 'action' => 'delete', 'label' => 'Hapus Alur Kerja', 'group' => 'Master Proyek'],

            // Content Management
            ['name' => 'content', 'action' => 'create', 'label' => 'Tambah Konten', 'group' => 'Manajemen Konten'],
            ['name' => 'content', 'action' => 'read', 'label' => 'Lihat Konten', 'group' => 'Manajemen Konten'],
            ['name' => 'content', 'action' => 'update', 'label' => 'Ubah Konten', 'group' => 'Manajemen Konten'],
            ['name' => 'content', 'action' => 'delete', 'label' => 'Hapus Konten', 'group' => 'Manajemen Konten'],

            ['name' => 'home_section', 'action' => 'create', 'label' => 'Tambah Section Beranda', 'group' => 'Manajemen Konten'],
            ['name' => 'home_section', 'action' => 'read', 'label' => 'Lihat Section Beranda', 'group' => 'Manajemen Konten'],
            ['name' => 'home_section', 'action' => 'update', 'label' => 'Ubah Section Beranda', 'group' => 'Manajemen Konten'],
            ['name' => 'home_section', 'action' => 'delete', 'label' => 'Hapus Section Beranda', 'group' => 'Manajemen Konten'],

            ['name' => 'latest_information', 'action' => 'create', 'label' => 'Tambah Informasi Terkini', 'group' => 'Manajemen Konten'],
            ['name' => 'latest_information', 'action' => 'read', 'label' => 'Lihat Informasi Terkini', 'group' => 'Manajemen Konten'],
            ['name' => 'latest_information', 'action' => 'update', 'label' => 'Ubah Informasi Terkini', 'group' => 'Manajemen Konten'],
            ['name' => 'latest_information', 'action' => 'delete', 'label' => 'Hapus Informasi Terkini', 'group' => 'Manajemen Konten'],

            ['name' => 'quick_link', 'action' => 'create', 'label' => 'Tambah Link Cepat', 'group' => 'Manajemen Konten'],
            ['name' => 'quick_link', 'action' => 'read', 'label' => 'Lihat Link Cepat', 'group' => 'Manajemen Konten'],
            ['name' => 'quick_link', 'action' => 'update', 'label' => 'Ubah Link Cepat', 'group' => 'Manajemen Konten'],
            ['name' => 'quick_link', 'action' => 'delete', 'label' => 'Hapus Link Cepat', 'group' => 'Manajemen Konten'],

            ['name' => 'kategori_berita', 'action' => 'create', 'label' => 'Tambah Kategori Berita', 'group' => 'Manajemen Konten'],
            ['name' => 'kategori_berita', 'action' => 'read', 'label' => 'Lihat Kategori Berita', 'group' => 'Manajemen Konten'],
            ['name' => 'kategori_berita', 'action' => 'update', 'label' => 'Ubah Kategori Berita', 'group' => 'Manajemen Konten'],
            ['name' => 'kategori_berita', 'action' => 'delete', 'label' => 'Hapus Kategori Berita', 'group' => 'Manajemen Konten'],

            ['name' => 'tag', 'action' => 'create', 'label' => 'Tambah Tag', 'group' => 'Manajemen Konten'],
            ['name' => 'tag', 'action' => 'read', 'label' => 'Lihat Tag', 'group' => 'Manajemen Konten'],
            ['name' => 'tag', 'action' => 'update', 'label' => 'Ubah Tag', 'group' => 'Manajemen Konten'],
            ['name' => 'tag', 'action' => 'delete', 'label' => 'Hapus Tag', 'group' => 'Manajemen Konten'],

            ['name' => 'article', 'action' => 'create', 'label' => 'Tambah Berita & Artikel', 'group' => 'Manajemen Konten'],
            ['name' => 'article', 'action' => 'read', 'label' => 'Lihat Berita & Artikel', 'group' => 'Manajemen Konten'],
            ['name' => 'article', 'action' => 'update', 'label' => 'Ubah Berita & Artikel', 'group' => 'Manajemen Konten'],
            ['name' => 'article', 'action' => 'delete', 'label' => 'Hapus Berita & Artikel', 'group' => 'Manajemen Konten'],

            ['name' => 'publication', 'action' => 'create', 'label' => 'Tambah Publikasi', 'group' => 'Manajemen Konten'],
            ['name' => 'publication', 'action' => 'read', 'label' => 'Lihat Publikasi', 'group' => 'Manajemen Konten'],
            ['name' => 'publication', 'action' => 'update', 'label' => 'Ubah Publikasi', 'group' => 'Manajemen Konten'],
            ['name' => 'publication', 'action' => 'delete', 'label' => 'Hapus Publikasi', 'group' => 'Manajemen Konten'],

            ['name' => 'gallery', 'action' => 'create', 'label' => 'Tambah Galeri', 'group' => 'Manajemen Konten'],
            ['name' => 'gallery', 'action' => 'read', 'label' => 'Lihat Galeri', 'group' => 'Manajemen Konten'],
            ['name' => 'gallery', 'action' => 'update', 'label' => 'Ubah Galeri', 'group' => 'Manajemen Konten'],
            ['name' => 'gallery', 'action' => 'delete', 'label' => 'Hapus Galeri', 'group' => 'Manajemen Konten'],

            ['name' => 'showcase', 'action' => 'create', 'label' => 'Tambah Showcase', 'group' => 'Manajemen Konten'],
            ['name' => 'showcase', 'action' => 'read', 'label' => 'Lihat Showcase', 'group' => 'Manajemen Konten'],
            ['name' => 'showcase', 'action' => 'update', 'label' => 'Ubah Showcase', 'group' => 'Manajemen Konten'],
            ['name' => 'showcase', 'action' => 'delete', 'label' => 'Hapus Showcase', 'group' => 'Manajemen Konten'],

            ['name' => 'agenda', 'action' => 'create', 'label' => 'Tambah Agenda', 'group' => 'Manajemen Konten'],
            ['name' => 'agenda', 'action' => 'read', 'label' => 'Lihat Agenda', 'group' => 'Manajemen Konten'],
            ['name' => 'agenda', 'action' => 'update', 'label' => 'Ubah Agenda', 'group' => 'Manajemen Konten'],
            ['name' => 'agenda', 'action' => 'delete', 'label' => 'Hapus Agenda', 'group' => 'Manajemen Konten'],

            // Navigation Management
            ['name' => 'menu', 'action' => 'create', 'label' => 'Tambah Menu Utama', 'group' => 'Manajemen Navigasi'],
            ['name' => 'menu', 'action' => 'read', 'label' => 'Lihat Menu Utama', 'group' => 'Manajemen Navigasi'],
            ['name' => 'menu', 'action' => 'update', 'label' => 'Ubah Menu Utama', 'group' => 'Manajemen Navigasi'],
            ['name' => 'menu', 'action' => 'delete', 'label' => 'Hapus Menu Utama', 'group' => 'Manajemen Navigasi'],

            ['name' => 'sub_menu', 'action' => 'create', 'label' => 'Tambah Sub Menu', 'group' => 'Manajemen Navigasi'],
            ['name' => 'sub_menu', 'action' => 'read', 'label' => 'Lihat Sub Menu', 'group' => 'Manajemen Navigasi'],
            ['name' => 'sub_menu', 'action' => 'update', 'label' => 'Ubah Sub Menu', 'group' => 'Manajemen Navigasi'],
            ['name' => 'sub_menu', 'action' => 'delete', 'label' => 'Hapus Sub Menu', 'group' => 'Manajemen Navigasi'],

            ['name' => 'child_sub_menu', 'action' => 'create', 'label' => 'Tambah Child Menu', 'group' => 'Manajemen Navigasi'],
            ['name' => 'child_sub_menu', 'action' => 'read', 'label' => 'Lihat Child Menu', 'group' => 'Manajemen Navigasi'],
            ['name' => 'child_sub_menu', 'action' => 'update', 'label' => 'Ubah Child Menu', 'group' => 'Manajemen Navigasi'],
            ['name' => 'child_sub_menu', 'action' => 'delete', 'label' => 'Hapus Child Menu', 'group' => 'Manajemen Navigasi'],

            // Activity Log
            ['name' => 'activity_log', 'action' => 'read', 'label' => 'Lihat Aktivitas Terkini', 'group' => 'Log Aktivitas'],

            // System Setting
            ['name' => 'setting', 'action' => 'read', 'label' => 'Lihat Pengaturan', 'group' => 'Pengaturan Sistem'],
            ['name' => 'setting', 'action' => 'update', 'label' => 'Ubah Pengaturan', 'group' => 'Pengaturan Sistem'],
        ];

        foreach ($permissions as $p) {
            $code = "{$p['name']}.{$p['action']}";
            DB::table('ms_permissions')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $p['name'],
                    'action' => $p['action'],
                    'label' => $p['label'],
                    'group' => $p['group'],
                    'is_system' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // Assign all permissions to super_admin and admin_pusat
        $allPermissionIds = DB::table('ms_permissions')->pluck('id');
        $superAdmin = DB::table('ms_roles')->where('code', 'super_admin')->first();
        if ($superAdmin) {
            foreach ($allPermissionIds as $pId) {
                DB::table('ms_role_permissions')->updateOrInsert(
                    ['role_id' => $superAdmin->id, 'permission_id' => $pId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        $adminPusat = DB::table('ms_roles')->where('code', 'admin_pusat')->first();
        if ($adminPusat) {
            foreach ($allPermissionIds as $pId) {
                DB::table('ms_role_permissions')->updateOrInsert(
                    ['role_id' => $adminPusat->id, 'permission_id' => $pId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // Assign profile.read to all roles so any authenticated user can view their own profile
        $profilePerm = DB::table('ms_permissions')->where('code', 'profile.read')->first();
        if ($profilePerm) {
            $allRoles = DB::table('ms_roles')->pluck('id');
            foreach ($allRoles as $rId) {
                DB::table('ms_role_permissions')->updateOrInsert(
                    ['role_id' => $rId, 'permission_id' => $profilePerm->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
