<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MsLayout;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLayouts();
    }

    public function test_can_get_paginated_list()
    {
        $menu = Menu::factory()->create();
        SubMenu::factory()->count(5)->create(['menu_id' => $menu->id]);
        $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/sub-menu');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data', 'meta']]);
    }

    public function test_can_create_sub_menu()
    {
        $menu = Menu::factory()->create();
        $layout = MsLayout::first();
        $this->actingAsSuperAdmin();
        $data = [
            'menu_id' => $menu->id,
            'title' => 'SubMenu Test',
            'description' => 'SubMenu Description Test',
            'slug' => 'submenu-test',
            'order' => 1,
            'is_active' => true,
            'has_child' => false,
            'type' => 'text',
            'layout_id' => $layout->id,
            'content' => [
                'content' => 'Sample text',
            ],
        ];

        $response = $this->postJson('/api/sub-menu', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);

        $this->assertDatabaseHas('sub_menus', [
            'menu_id' => $menu->id,
            'slug' => 'submenu-test',
            'description' => 'SubMenu Description Test',
        ]);
    }

    public function test_can_get_sub_menu_detail()
    {
        $menu = Menu::factory()->create();
        $subMenu = SubMenu::factory()->create([
            'menu_id' => $menu->id,
            'description' => 'Sample Detail Description',
        ]);
        $this->actingAsSuperAdmin();

        $response = $this->getJson("/api/sub-menu/{$subMenu->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
        $this->assertEquals('Sample Detail Description', $response->json('data.description'));
    }

    public function test_returns_404_for_nonexistent_sub_menu()
    {
        $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/sub-menu/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_sub_menu()
    {
        $menu = Menu::factory()->create();
        $subMenu = SubMenu::factory()->create([
            'menu_id' => $menu->id,
            'has_child' => true,
        ]);
        $this->actingAsSuperAdmin();
        $data = [
            'menu_id' => $menu->id,
            'title' => 'Updated SubMenu',
            'description' => 'Updated SubMenu Description',
            'slug' => 'updated-submenu',
            'has_child' => true,
            'is_active' => true,
            'order' => 1,
        ];

        $response = $this->putJson("/api/sub-menu/{$subMenu->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);

        $this->assertDatabaseHas('sub_menus', [
            'id' => $subMenu->id,
            'description' => 'Updated SubMenu Description',
        ]);
    }

    public function test_sub_menu_description_exceeding_max_fails_validation()
    {
        $menu = Menu::factory()->create();
        $layout = MsLayout::first();
        $this->actingAsSuperAdmin();
        $data = [
            'menu_id' => $menu->id,
            'title' => 'SubMenu Test',
            'description' => str_repeat('a', 256),
            'slug' => 'submenu-test-max',
            'order' => 1,
            'is_active' => true,
            'has_child' => false,
            'type' => 'text',
            'layout_id' => $layout->id,
        ];

        $response = $this->postJson('/api/sub-menu', $data);

        $response->assertStatus(422);
    }

    public function test_can_delete_sub_menu()
    {
        $menu = Menu::factory()->create();
        $subMenu = SubMenu::factory()->create(['menu_id' => $menu->id]);
        $this->actingAsSuperAdmin();

        $response = $this->deleteJson("/api/sub-menu/{$subMenu->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('sub_menus', ['id' => $subMenu->id]);
    }

    public function test_returns_404_when_deleting_nonexistent_sub_menu()
    {
        $this->actingAsSuperAdmin();

        $response = $this->deleteJson('/api/sub-menu/99999');

        $response->assertStatus(404);
    }
}
