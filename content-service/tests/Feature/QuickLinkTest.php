<?php

namespace Tests\Feature;

use App\Models\QuickLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_paginated_list()
    {
        QuickLink::factory()->count(5)->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/quick-link/');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data', 'meta']]);
    }

    public function test_can_create_quick_link()
    {
        $user = User::factory()->create();
        $data = QuickLink::factory()->make()->toArray();

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/quick-link/', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_quick_link_detail()
    {
        $quickLink = QuickLink::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/quick-link/{$quickLink->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_quick_link()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/quick-link/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_quick_link()
    {
        $quickLink = QuickLink::factory()->create();
        $user = User::factory()->create();
        $data = ['title' => 'Updated Quick Link Title'];

        $response = $this->actingAs($user, 'admin')
            ->putJson("/api/quick-link/{$quickLink->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_delete_quick_link()
    {
        $quickLink = QuickLink::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson("/api/quick-link/{$quickLink->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_when_deleting_nonexistent_quick_link()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson('/api/quick-link/99999');

        $response->assertStatus(404);
    }
}
