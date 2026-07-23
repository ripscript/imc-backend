<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_file()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/upload', ['file' => $file]);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_compress_image()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/compress-image', ['file' => $file]);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_download_file()
    {
        $response = $this->getJson('/api/download?filepath=sample.pdf');

        $response->assertStatus(200);
    }

    public function test_returns_404_when_downloading_nonexistent_file()
    {
        $response = $this->getJson('/api/download?filepath=nonexistent.pdf');

        $response->assertStatus(404);
    }

    public function test_can_show_file()
    {
        $response = $this->getJson('/api/show?filepath=sample.pdf');

        $response->assertStatus(200);
    }

    public function test_returns_404_when_showing_nonexistent_file()
    {
        $response = $this->getJson('/api/show?filepath=nonexistent.pdf');

        $response->assertStatus(404);
    }

    public function test_can_lock_pdf()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/lock-pdf', [
                'file' => $file,
                'password' => 'secret123',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_lock_image()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/lock-image', [
                'file' => $file,
                'password' => 'secret123',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_file_base64()
    {
        $response = $this->getJson('/api/base64?filepath=sample.pdf');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_when_getting_base64_for_nonexistent_file()
    {
        $response = $this->postJson('/api/base64', ['filepath' => 'nonexistent.pdf']);

        $response->assertStatus(404);
    }
}
