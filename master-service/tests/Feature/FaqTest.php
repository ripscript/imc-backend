<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedMasterTables();
        $this->actingAsAdmin();
    }

    private function seedMasterTables(): void
    {
        DB::statement('CREATE TABLE IF NOT EXISTS ms_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, code TEXT, created_at DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE IF NOT EXISTS ms_kategori_faq (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE IF NOT EXISTS ms_faq (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, description TEXT, is_active INTEGER, kategori_faq_id INTEGER, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)');
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
        $this->getJson('/api/faq')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_create_and_detail_faq(): void
    {
        $kategoriId = DB::table('ms_kategori_faq')->insertGetId(['name' => 'Umum', 'created_by' => 'test', 'updated_by' => 'test', 'created_at' => now(), 'updated_at' => now()]);
        $response = $this->postJson('/api/faq', [
            'title' => 'Judul FAQ',
            'description' => 'Deskripsi FAQ',
            'is_active' => true,
            'kategori_faq_id' => $kategoriId,
        ]);
        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Judul FAQ');

        $id = $response->json('data.id');
        $this->getJson("/api/faq/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $id);
    }

    public function test_update_faq(): void
    {
        $kategoriId = DB::table('ms_kategori_faq')->insertGetId(['name' => 'Umum', 'created_by' => 'test', 'updated_by' => 'test', 'created_at' => now(), 'updated_at' => now()]);
        $faqId = DB::table('ms_faq')->insertGetId([
            'title' => 'Lama',
            'description' => 'Desc',
            'is_active' => 1,
            'kategori_faq_id' => $kategoriId,
            'created_by' => 'test',
            'updated_by' => 'test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->putJson("/api/faq/{$faqId}", ['title' => 'Baru', 'description' => 'Desc', 'is_active' => true, 'kategori_faq_id' => $kategoriId])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Baru');
    }

    public function test_delete_faq(): void
    {
        $kategoriId = DB::table('ms_kategori_faq')->insertGetId(['name' => 'Umum', 'created_by' => 'test', 'updated_by' => 'test', 'created_at' => now(), 'updated_at' => now()]);
        $faqId = DB::table('ms_faq')->insertGetId([
            'title' => 'Hapus',
            'description' => 'Desc',
            'is_active' => 1,
            'kategori_faq_id' => $kategoriId,
            'created_by' => 'test',
            'updated_by' => 'test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deleteJson("/api/faq/{$faqId}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('ms_faq', ['id' => $faqId]);
        $this->assertNotNull(DB::table('ms_faq')->where('id', $faqId)->value('deleted_at'));
    }
}
