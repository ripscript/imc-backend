<?php

namespace Tests\Feature;

use App\Models\ChildSubMenu;
use App\Models\Menu;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildSubMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_paginated_list()
    {
        Menu::factory()->create();
        SubMenu::factory()->create();
        ChildSubMenu::factory()->count(5)->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/child-sub-menu/');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data', 'meta']]);
    }

    public function test_can_create_child_sub_menu()
    {
        $subMenu = SubMenu::factory()->create();
        $user = User::factory()->create();
        $data = ChildSubMenu::factory()->make(['sub_menu_id' => $subMenu->id])->toArray();

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/child-sub-menu/', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_child_sub_menu_detail()
    {
        $childSubMenu = ChildSubMenu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/child-sub-menu/{$childSubMenu->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_child_sub_menu()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/child-sub-menu/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_child_sub_menu()
    {
        $childSubMenu = ChildSubMenu::factory()->create();
        $user = User::factory()->create();
        $data = ['title' => 'Updated Child Sub Menu Title'];

        $response = $this->actingAs($user, 'admin')
            ->putJson("/api/child-sub-menu/{$childSubMenu->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_delete_child_sub_menu()
    {
        $childSubMenu = ChildSubMenu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson("/api/child-sub-menu/{$childSubMenu->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_when_deleting_nonexistent_child_sub_menu()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson('/api/child-sub-menu/99999');

        $response->assertStatus(404);
    }
}
