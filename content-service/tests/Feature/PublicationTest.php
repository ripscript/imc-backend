<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentDetailPublication;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_side_menu()
    {
        Menu::factory()->create(['is_show_side_menu' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/publication/side-menu');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_paginated_list_by_content()
    {
        $content = Content::factory()->create();
        ContentDetailPublication::factory()->count(5)->create(['content_id' => $content->id]);
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/publication/list/{$content->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data', 'meta']]);
    }

    public function test_can_create_publication()
    {
        $user = User::factory()->create();
        $data = ContentDetailPublication::factory()->make()->toArray();

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/publication/', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_publication_detail()
    {
        $publication = ContentDetailPublication::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/publication/{$publication->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_publication()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/publication/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_publication()
    {
        $publication = ContentDetailPublication::factory()->create();
        $user = User::factory()->create();
        $data = ['title' => 'Updated Publication Title'];

        $response = $this->actingAs($user, 'admin')
            ->putJson("/api/publication/{$publication->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_delete_publication()
    {
        $publication = ContentDetailPublication::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson("/api/publication/{$publication->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_when_deleting_nonexistent_publication()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson('/api/publication/99999');

        $response->assertStatus(404);
    }
}
