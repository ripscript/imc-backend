<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentDetailGallery;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_side_menu()
    {
        Menu::factory()->create(['is_show_side_menu' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/gallery/side-menu');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_paginated_list_by_content()
    {
        $content = Content::factory()->create();
        ContentDetailGallery::factory()->count(5)->create(['content_id' => $content->id]);
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/gallery/list/{$content->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data', 'meta']]);
    }

    public function test_can_create_gallery()
    {
        $user = User::factory()->create();
        $data = ContentDetailGallery::factory()->make()->toArray();

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/gallery/', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_gallery_detail()
    {
        $gallery = ContentDetailGallery::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/gallery/{$gallery->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_gallery()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/gallery/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_gallery()
    {
        $gallery = ContentDetailGallery::factory()->create();
        $user = User::factory()->create();
        $data = ['title' => 'Updated Gallery Title'];

        $response = $this->actingAs($user, 'admin')
            ->putJson("/api/gallery/{$gallery->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_delete_gallery()
    {
        $gallery = ContentDetailGallery::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson("/api/gallery/{$gallery->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_when_deleting_nonexistent_gallery()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson('/api/gallery/99999');

        $response->assertStatus(404);
    }
}
