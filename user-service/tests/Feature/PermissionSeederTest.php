<?php

namespace Tests\Feature;

use App\Models\MsRole;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_seeder_seeds_all_specified_permissions(): void
    {
        MsRole::create([
            'name' => 'Superadmin',
            'code' => 'super_admin',
            'is_system' => true,
        ]);
        MsRole::create([
            'name' => 'Admin Pusat',
            'code' => 'admin_pusat',
            'is_system' => true,
        ]);

        $this->seed(PermissionSeeder::class);

        $expectedResources = [
            'dashboard',
            'project',
            'permission',
            'client',
            'partner',
            'project_category',
            'document_type',
            'workflow',
            'kategori_berita',
            'tag',
            'approval',
            'home_section',
            'latest_information',
            'quick_link',
            'menu',
            'sub_menu',
            'child_sub_menu',
            'article',
            'publication',
            'gallery',
            'showcase',
            'agenda',
            'activity_log',
            'setting',
        ];

        foreach ($expectedResources as $resource) {
            $this->assertDatabaseHas('ms_permissions', [
                'name' => $resource,
                'is_system' => true,
            ]);
        }
    }
}
