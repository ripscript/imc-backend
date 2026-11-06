<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MsLayout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLayouts();
    }

    public function test_can_get_paginated_list()
    {
        Menu::factory()->count(5)->create();
        $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/menu');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data', 'meta']]);
    }

    public function test_can_create_menu()
    {
        $this->actingAsSuperAdmin();
        $layout = MsLayout::first();
        $data = [
            'title' => 'Menu Test',
            'description' => 'Menu Description Test',
            'slug' => 'menu-test',
            'order' => 1,
            'is_active' => true,
            'has_sub' => false,
            'is_show_front' => false,
            'type' => 'text',
            'layout_id' => $layout->id,
            'content' => [
                'content' => 'Sample text',
            ],
        ];

        $response = $this->postJson('/api/menu', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);

        $this->assertDatabaseHas('menus', [
            'slug' => 'menu-test',
            'description' => 'Menu Description Test',
        ]);
    }

    public function test_can_get_menu_detail()
    {
        $menu = Menu::factory()->create([
            'description' => 'Sample Detail Description',
        ]);
        $this->actingAsSuperAdmin();

        $response = $this->getJson("/api/menu/{$menu->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
        $this->assertEquals('Sample Detail Description', $response->json('data.description'));
    }

    public function test_returns_404_for_nonexistent_menu()
    {
        $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/menu/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_menu()
    {
        $menu = Menu::factory()->create([
            'has_sub' => true,
        ]);
        $this->actingAsSuperAdmin();
        $data = [
            'title' => 'Updated Title',
            'description' => 'Updated Menu Description',
            'slug' => 'updated-title',
            'has_sub' => true,
            'is_active' => true,
            'is_show_front' => false,
            'order' => 1,
        ];

        $response = $this->putJson("/api/menu/{$menu->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'description' => 'Updated Menu Description',
        ]);
    }

    public function test_menu_description_exceeding_max_fails_validation()
    {
        $this->actingAsSuperAdmin();
        $layout = MsLayout::first();
        $data = [
            'title' => 'Menu Test',
            'description' => str_repeat('a', 256),
            'slug' => 'menu-test-max',
            'order' => 1,
            'is_active' => true,
            'has_sub' => false,
            'is_show_front' => false,
            'type' => 'text',
            'layout_id' => $layout->id,
        ];

        $response = $this->postJson('/api/menu', $data);

        $response->assertStatus(422);
    }

    public function test_can_delete_menu()
    {
        $menu = Menu::factory()->create();
        $this->actingAsSuperAdmin();

        $response = $this->deleteJson("/api/menu/{$menu->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }

    public function test_returns_404_when_deleting_nonexistent_menu()
    {
        $this->actingAsSuperAdmin();

        $response = $this->deleteJson('/api/menu/99999');

        $response->assertStatus(404);
    }
}
