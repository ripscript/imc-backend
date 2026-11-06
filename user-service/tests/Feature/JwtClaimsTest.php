<?php

namespace Tests\Feature;

use App\Models\MsPermission;
use App\Models\MsRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class JwtClaimsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_jwt_claims_contain_role_and_permissions(): void
    {
        $role = MsRole::create([
            'name' => 'Project Manager',
            'code' => 'project_manager',
            'is_active' => true,
        ]);

        $perm1 = MsPermission::create([
            'name' => 'project',
            'action' => 'create',
            'code' => 'project.create',
            'label' => 'Buat Proyek',
        ]);

        $perm2 = MsPermission::create([
            'name' => 'project',
            'action' => 'update',
            'code' => 'project.update',
            'label' => 'Ubah Proyek',
        ]);

        $role->permissions()->attach([$perm1->id, $perm2->id]);

        $user = User::create([
            'name' => 'Manager User',
            'email' => 'pm@example.com',
            'username' => 'pm_user',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'status' => 1,
            'gender' => 1,
        ]);

        $claims = $user->getJWTCustomClaims();
        $this->assertEquals('project_manager', $claims['role']);
        $this->assertContains('project.create', $claims['permissions']);
        $this->assertContains('project.update', $claims['permissions']);

        // Test with real JWT token generation
        $token = JWTAuth::fromUser($user);
        $payload = JWTAuth::setToken($token)->getPayload();

        $this->assertEquals('project_manager', $payload->get('role'));
        $this->assertEqualsCanonicalizing(['project.create', 'project.update'], $payload->get('permissions'));
    }

    public function test_api_request_with_bearer_token_authenticates_and_verifies_permissions(): void
    {
        config(['app.enable_rbac_permission' => true]);

        $role = MsRole::create([
            'name' => 'Auditor',
            'code' => 'super_admin', // allowed by check.role route middleware
            'is_active' => true,
        ]);

        $perm = MsPermission::create([
            'name' => 'permission',
            'action' => 'read',
            'code' => 'permission.read',
            'label' => 'Lihat Hak Akses',
        ]);

        $role->permissions()->attach($perm->id);

        $user = User::create([
            'name' => 'Auditor User',
            'email' => 'auditor@example.com',
            'username' => 'auditor',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'status' => 1,
            'gender' => 1,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)
            ->getJson('/api/permission');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
