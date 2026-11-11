<?php

namespace Tests\Feature;

use App\Models\MsPermission;
use App\Models\MsRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected MsRole $superAdminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = MsRole::create([
            'name' => 'Superadmin',
            'code' => 'super_admin',
            'is_system' => true,
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => bcrypt('password123'),
            'role_id' => $this->superAdminRole->id,
            'status' => 1,
            'gender' => 1,
        ]);
    }

    public function test_can_list_roles_with_permissions(): void
    {
        $perm = MsPermission::create([
            'name' => 'project',
            'action' => 'read',
            'code' => 'project.read',
            'label' => 'Lihat Proyek',
        ]);
        $this->superAdminRole->permissions()->attach($perm->id);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->getJson('/api/role');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_can_create_role_with_permissions(): void
    {
        $perm1 = MsPermission::create([
            'name' => 'doc',
            'action' => 'upload',
            'code' => 'doc.upload',
            'label' => 'Unggah Dokumen',
        ]);

        $perm2 = MsPermission::create([
            'name' => 'doc',
            'action' => 'download',
            'code' => 'doc.download',
            'label' => 'Unduh Dokumen',
        ]);

        $payload = [
            'name' => 'Dokumentator',
            'code' => 'dokumentator',
            'is_active' => true,
            'permission_ids' => [$perm1->id, $perm2->id],
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
            ->postJson('/api/role', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Dokumentator')
            ->assertJsonPath('data.code', 'dokumentator')
            ->assertJsonPath('data.is_system', false);

        $this->assertDatabaseHas('ms_roles', ['code' => 'dokumentator']);
        $createdRole = MsRole::where('code', 'dokumentator')->first();
        $this->assertCount(2, $createdRole->permissions);
    }

    public function test_can_update_role_and_sync_permissions(): void
    {
        $role = MsRole::create([
            'name' => 'Editor Konten',
            'code' => 'editor_konten',
            'is_active' => true,
        ]);

        $perm1 = MsPermission::create([
            'name' => 'article',
            'action' => 'write',
            'code' => 'article.write',
            'label' => 'Tulis Artikel',
        ]);
        $perm2 = MsPermission::create([
            'name' => 'article',
            'action' => 'publish',
            'code' => 'article.publish',
            'label' => 'Publikasi Artikel',
        ]);

        $role->permissions()->attach($perm1->id);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->putJson("/api/role/{$role->id}", [
                'name' => 'Senior Editor Konten',
                'is_active' => true,
                'permission_ids' => [$perm2->id], // syncs to perm2 only
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Senior Editor Konten');

        $role->refresh();
        $this->assertCount(1, $role->permissions);
        $this->assertEquals('article.publish', $role->permissions->first()->code);
    }

    public function test_cannot_delete_system_role(): void
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->deleteJson("/api/role/{$this->superAdminRole->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('ms_roles', ['id' => $this->superAdminRole->id]);
    }

    public function test_cannot_delete_role_with_active_users(): void
    {
        $role = MsRole::create([
            'name' => 'Staff Lapangan',
            'code' => 'staff_lapangan',
            'is_system' => false,
        ]);

        User::create([
            'name' => 'Staff 1',
            'email' => 'staff1@example.com',
            'username' => 'staff1',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'status' => 1,
            'gender' => 1,
        ]);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->deleteJson("/api/role/{$role->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('ms_roles', ['id' => $role->id]);
    }

    public function test_can_delete_unassigned_non_system_role(): void
    {
        $role = MsRole::create([
            'name' => 'Magang',
            'code' => 'magang',
            'is_system' => false,
        ]);

        $perm = MsPermission::create([
            'name' => 'task',
            'action' => 'view',
            'code' => 'task.view',
            'label' => 'Lihat Tugas',
        ]);
        $role->permissions()->attach($perm->id);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->deleteJson("/api/role/{$role->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('ms_roles', ['id' => $role->id]);
        $this->assertDatabaseMissing('ms_role_permissions', ['role_id' => $role->id]);
    }
}
