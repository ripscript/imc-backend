<?php

namespace Tests\Feature;

use App\Models\MsPermission;
use App\Models\MsRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_endpoint_returns_user_details_with_permissions(): void
    {
        $role = MsRole::create([
            'name' => 'Project Lead',
            'code' => 'project_lead',
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
            'action' => 'approve',
            'code' => 'project.approve',
            'label' => 'Setujui Proyek',
        ]);

        $role->permissions()->attach([$perm1->id, $perm2->id]);

        $user = User::create([
            'name' => 'Lead User',
            'email' => 'lead@example.com',
            'username' => 'lead_user',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'status' => 1,
            'gender' => 1,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)
            ->getJson('/api/auth/profile');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'email',
                    'username',
                    'name',
                    'role_id',
                    'role',
                    'permissions',
                ],
            ])
            ->assertJsonPath('data.permissions', ['project.create', 'project.approve']);
    }
}
