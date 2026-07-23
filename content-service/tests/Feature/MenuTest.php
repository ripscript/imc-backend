<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_paginated_list()
    {
        Menu::factory()->count(5)->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/menu/');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data', 'meta']]);
    }

    public function test_can_create_menu()
    {
        $user = User::factory()->create();
        $data = Menu::factory()->make()->toArray();

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/menu/', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_menu_detail()
    {
        $menu = Menu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/menu/{$menu->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_menu()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/menu/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_menu()
    {
        $menu = Menu::factory()->create();
        $user = User::factory()->create();
        $data = ['title' => 'Updated Title', 'slug' => 'updated-title'];

        $response = $this->actingAs($user, 'admin')
            ->putJson("/api/menu/{$menu->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_delete_menu()
    {
        $menu = Menu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson("/api/menu/{$menu->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_when_deleting_nonexistent_menu()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson('/api/menu/99999');

        $response->assertStatus(404);
    }
}
