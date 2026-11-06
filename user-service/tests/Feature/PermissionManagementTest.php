<?php

namespace Tests\Feature;

use App\Models\MsPermission;
use App\Models\MsRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected MsRole $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = MsRole::create([
            'name' => 'Superadmin',
            'code' => 'super_admin',
            'is_system' => true,
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => bcrypt('password123'),
            'role_id' => $this->adminRole->id,
            'status' => 1,
            'gender' => 1,
        ]);
    }

    public function test_can_list_permissions(): void
    {
        MsPermission::create([
            'name' => 'project',
            'action' => 'create',
            'code' => 'project.create',
            'label' => 'Tambah Proyek',
            'group' => 'Manajemen Proyek',
            'is_system' => true,
        ]);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->getJson('/api/permission');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'action', 'code', 'label', 'group', 'is_system'],
                    ],
                ],
            ]);
    }

    public function test_can_filter_permissions_by_group(): void
    {
        MsPermission::create([
            'name' => 'project',
            'action' => 'create',
            'code' => 'project.create',
            'label' => 'Tambah Proyek',
            'group' => 'Manajemen Proyek',
        ]);

        MsPermission::create([
            'name' => 'user',
            'action' => 'create',
            'code' => 'user.create',
            'label' => 'Tambah Pengguna',
            'group' => 'Manajemen Pengguna',
        ]);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->getJson('/api/permission?group=Manajemen+Proyek&all=true');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('project.create', $data[0]['code']);
    }

    public function test_can_create_permission_with_generated_code(): void
    {
        $payload = [
            'name' => 'report',
            'action' => 'export',
            'label' => 'Unduh Laporan',
            'group' => 'Pelaporan',
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
            ->postJson('/api/permission', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.code', 'report.export')
            ->assertJsonPath('data.name', 'report')
            ->assertJsonPath('data.action', 'export')
            ->assertJsonPath('data.label', 'Unduh Laporan')
            ->assertJsonPath('data.is_system', false);

        $this->assertDatabaseHas('ms_permissions', [
            'code' => 'report.export',
        ]);
    }

    public function test_cannot_create_duplicate_permission_code(): void
    {
        MsPermission::create([
            'name' => 'report',
            'action' => 'export',
            'code' => 'report.export',
            'label' => 'Unduh Laporan',
        ]);

        $payload = [
            'name' => 'report',
            'action' => 'export',
            'label' => 'Unduh Laporan Baru',
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
            ->postJson('/api/permission', $payload);

        $response->assertStatus(422);
    }

    public function test_can_update_permission_label_and_group_while_code_remains_immutable(): void
    {
        $permission = MsPermission::create([
            'name' => 'finance',
            'action' => 'audit',
            'code' => 'finance.audit',
            'label' => 'Audit Keuangan',
            'group' => 'Keuangan',
        ]);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->putJson("/api/permission/{$permission->id}", [
                'label' => 'Audit Keuangan Diperbarui',
                'group' => 'Audit & Kepatuhan',
                'name' => 'hacked_name', // should be ignored
                'action' => 'hacked_action', // should be ignored
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.label', 'Audit Keuangan Diperbarui')
            ->assertJsonPath('data.group', 'Audit & Kepatuhan')
            ->assertJsonPath('data.code', 'finance.audit')
            ->assertJsonPath('data.name', 'finance')
            ->assertJsonPath('data.action', 'audit');
    }

    public function test_cannot_delete_system_permission(): void
    {
        $permission = MsPermission::create([
            'name' => 'auth',
            'action' => 'login',
            'code' => 'auth.login',
            'label' => 'Login',
            'is_system' => true,
        ]);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->deleteJson("/api/permission/{$permission->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('ms_permissions', ['id' => $permission->id]);
    }

    public function test_can_delete_non_system_permission(): void
    {
        $permission = MsPermission::create([
            'name' => 'temp',
            'action' => 'test',
            'code' => 'temp.test',
            'label' => 'Temporary Test',
            'is_system' => false,
        ]);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->deleteJson("/api/permission/{$permission->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('ms_permissions', ['id' => $permission->id]);
    }
}
