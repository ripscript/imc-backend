<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_index()
    {
        Content::factory()->count(5)->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/section/');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_create_section()
    {
        $user = User::factory()->create();
        $data = Content::factory()->make()->toArray();

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/section/', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_section_detail()
    {
        $content = Content::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/section/{$content->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_section()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/section/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_section()
    {
        $content = Content::factory()->create();
        $user = User::factory()->create();
        $data = ['section_title' => 'Updated Section Title'];

        $response = $this->actingAs($user, 'admin')
            ->putJson("/api/section/{$content->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_delete_section()
    {
        $content = Content::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson("/api/section/{$content->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_when_deleting_nonexistent_section()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson('/api/section/99999');

        $response->assertStatus(404);
    }
}
