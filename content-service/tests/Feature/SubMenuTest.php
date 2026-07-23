<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_paginated_list()
    {
        Menu::factory()->create();
        SubMenu::factory()->count(5)->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/sub-menu/');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data', 'meta']]);
    }

    public function test_can_create_sub_menu()
    {
        $menu = Menu::factory()->create();
        $user = User::factory()->create();
        $data = SubMenu::factory()->make(['menu_id' => $menu->id])->toArray();

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/sub-menu/', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_sub_menu_detail()
    {
        $subMenu = SubMenu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/sub-menu/{$subMenu->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_sub_menu()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/sub-menu/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_sub_menu()
    {
        $subMenu = SubMenu::factory()->create();
        $user = User::factory()->create();
        $data = ['title' => 'Updated Sub Menu Title'];

        $response = $this->actingAs($user, 'admin')
            ->putJson("/api/sub-menu/{$subMenu->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_delete_sub_menu()
    {
        $subMenu = SubMenu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson("/api/sub-menu/{$subMenu->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_when_deleting_nonexistent_sub_menu()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson('/api/sub-menu/99999');

        $response->assertStatus(404);
    }
}
