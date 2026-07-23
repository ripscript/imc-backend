<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KategoriFaqTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('CREATE TABLE IF NOT EXISTS ms_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, code TEXT, created_at DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE IF NOT EXISTS ms_kategori_faq (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)');
        $this->actingAsAdmin();
    }

    private function actingAsAdmin(): void
    {
        $roleId = DB::table('ms_roles')->insertGetId(['name' => 'Super Admin', 'code' => 'super_admin', 'created_at' => now(), 'updated_at' => now()]);
        $user = User::factory()->create(['role_id' => $roleId]);
        $token = auth('admin')->login($user);
        $this->withHeader('Authorization', 'Bearer '.$token);
    }

    public function test_list_returns_success_response(): void
    {
        $this->getJson('/api/kategori-faq')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_crud_kategori_faq(): void
    {
        $response = $this->postJson('/api/kategori-faq', ['name' => 'Umum']);
        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Umum');

        $id = $response->json('data.id');

        $this->getJson("/api/kategori-faq/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Umum');

        $this->putJson("/api/kategori-faq/{$id}", ['name' => 'Umum Updated'])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Umum Updated');

        $this->deleteJson("/api/kategori-faq/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
