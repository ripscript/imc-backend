<?php

namespace Tests\Feature;

use App\Models\MsPermission;
use App\Models\MsRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class CheckPermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['jwt.error:admin', 'check.permission:test.access'])->get('/api/test-permission-gate', function () {
            return response()->json(['status' => 'ok', 'message' => 'Passed']);
        });
    }

    public function test_bypasses_check_when_enable_rbac_permission_is_false(): void
    {
        config(['app.enable_rbac_permission' => false]);

        $role = MsRole::create([
            'name' => 'Regular Role',
            'code' => 'regular_role',
        ]);

        $user = User::create([
            'name' => 'User Without Permission',
            'email' => 'noperm@example.com',
            'username' => 'noperm',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 1,
            'gender' => 1,
        ]);

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/test-permission-gate');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    public function test_bypasses_check_for_super_admin_when_flag_is_true(): void
    {
        config(['app.enable_rbac_permission' => true]);

        $superAdminRole = MsRole::create([
            'name' => 'Superadmin',
            'code' => 'super_admin',
        ]);

        $superAdmin = User::create([
            'name' => 'Root Admin',
            'email' => 'root@example.com',
            'username' => 'root',
            'password' => bcrypt('password'),
            'role_id' => $superAdminRole->id,
            'status' => 1,
            'gender' => 1,
        ]);

        $token = JWTAuth::fromUser($superAdmin);

        $response = $this->withToken($token)
            ->getJson('/api/test-permission-gate');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    public function test_allows_access_when_user_has_required_permission(): void
    {
        config(['app.enable_rbac_permission' => true]);

        $role = MsRole::create([
            'name' => 'Authorized Role',
            'code' => 'authorized_role',
        ]);

        $perm = MsPermission::create([
            'name' => 'test',
            'action' => 'access',
            'code' => 'test.access',
            'label' => 'Test Access',
        ]);
        $role->permissions()->attach($perm->id);

        $user = User::create([
            'name' => 'Authorized User',
            'email' => 'authuser@example.com',
            'username' => 'authuser',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 1,
            'gender' => 1,
        ]);

        // Token generates with custom claims containing 'test.access'
        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)
            ->getJson('/api/test-permission-gate');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    public function test_denies_access_when_user_lacks_required_permission(): void
    {
        config(['app.enable_rbac_permission' => true]);

        $role = MsRole::create([
            'name' => 'Unauthorized Role',
            'code' => 'unauthorized_role',
        ]);

        $user = User::create([
            'name' => 'Unauthorized User',
            'email' => 'unauthuser@example.com',
            'username' => 'unauthuser',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 1,
            'gender' => 1,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)
            ->getJson('/api/test-permission-gate');

        $response->assertStatus(403);
    }

    public function test_gate_allows_access_when_jwt_contains_permission(): void
    {
        config(['app.enable_rbac_permission' => true]);

        $role = MsRole::create([
            'name' => 'Staff',
            'code' => 'staff',
        ]);

        $perm = MsPermission::create([
            'name' => 'test',
            'action' => 'access',
            'code' => 'test.access',
            'label' => 'Test Access',
        ]);
        $role->permissions()->attach($perm->id);

        $user = User::create([
            'name' => 'Staff User',
            'email' => 'staff_jwt@example.com',
            'username' => 'staff_jwt',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 1,
            'gender' => 1,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)
            ->getJson('/api/test-permission-gate');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    public function test_gate_denies_access_when_jwt_lacks_permission(): void
    {
        config(['app.enable_rbac_permission' => true]);

        $role = MsRole::create([
            'name' => 'No Perm Role',
            'code' => 'no_perm_role',
        ]);

        $user = User::create([
            'name' => 'No Perm User',
            'email' => 'no_perm@example.com',
            'username' => 'no_perm',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 1,
            'gender' => 1,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)
            ->getJson('/api/test-permission-gate');

        $response->assertStatus(403);
    }
}
