<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentDetailRepository;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_side_menu()
    {
        Menu::factory()->create(['is_show_side_menu' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/repository/side-menu');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_paginated_list_by_content()
    {
        $content = Content::factory()->create();
        ContentDetailRepository::factory()->count(5)->create(['content_id' => $content->id]);
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/repository/list/{$content->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data', 'meta']]);
    }

    public function test_can_create_repository()
    {
        $user = User::factory()->create();
        $data = ContentDetailRepository::factory()->make()->toArray();

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/repository/', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_repository_detail()
    {
        $repository = ContentDetailRepository::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/repository/{$repository->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_repository()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/repository/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_repository()
    {
        $repository = ContentDetailRepository::factory()->create();
        $user = User::factory()->create();
        $data = ['title' => 'Updated Repository Title'];

        $response = $this->actingAs($user, 'admin')
            ->putJson("/api/repository/{$repository->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_delete_repository()
    {
        $repository = ContentDetailRepository::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson("/api/repository/{$repository->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_when_deleting_nonexistent_repository()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson('/api/repository/99999');

        $response->assertStatus(404);
    }
}
