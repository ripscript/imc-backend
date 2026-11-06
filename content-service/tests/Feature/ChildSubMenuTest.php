<?php

namespace Tests\Feature;

use App\Models\ChildSubMenu;
use App\Models\Menu;
use App\Models\MsLayout;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildSubMenuTest extends TestCase
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
        $subMenu = SubMenu::factory()->create(['menu_id' => $menu->id, 'has_child' => true]);
        ChildSubMenu::factory()->count(5)->create(['sub_menu_id' => $subMenu->id]);
        $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/child-sub-menu');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data', 'meta']]);
    }

    public function test_can_create_child_sub_menu()
    {
        $menu = Menu::factory()->create();
        $subMenu = SubMenu::factory()->create(['menu_id' => $menu->id, 'has_child' => true]);
        $layout = MsLayout::first();
        $this->actingAsSuperAdmin();
        $data = [
            'sub_menu_id' => $subMenu->id,
            'title' => 'ChildSubMenu Test',
            'description' => 'ChildSubMenu Description Test',
            'slug' => 'childsubmenu-test',
            'order' => 1,
            'is_active' => true,
            'type' => 'text',
            'layout_id' => $layout->id,
            'content' => [
                'content' => 'Sample text',
            ],
        ];

        $response = $this->postJson('/api/child-sub-menu', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);

        $this->assertDatabaseHas('child_sub_menus', [
            'sub_menu_id' => $subMenu->id,
            'slug' => 'childsubmenu-test',
            'description' => 'ChildSubMenu Description Test',
        ]);
    }

    public function test_can_get_child_sub_menu_detail()
    {
        $menu = Menu::factory()->create();
        $subMenu = SubMenu::factory()->create(['menu_id' => $menu->id, 'has_child' => true]);
        $childSubMenu = ChildSubMenu::factory()->create([
            'sub_menu_id' => $subMenu->id,
            'description' => 'Sample Detail Description',
        ]);
        $this->actingAsSuperAdmin();

        $response = $this->getJson("/api/child-sub-menu/{$childSubMenu->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
        $this->assertEquals('Sample Detail Description', $response->json('data.description'));
    }

    public function test_returns_404_for_nonexistent_child_sub_menu()
    {
        $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/child-sub-menu/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_child_sub_menu()
    {
        $menu = Menu::factory()->create();
        $subMenu = SubMenu::factory()->create(['menu_id' => $menu->id, 'has_child' => true]);
        $layout = MsLayout::first();
        $childSubMenu = ChildSubMenu::factory()->create([
            'sub_menu_id' => $subMenu->id,
        ]);
        \App\Models\Content::factory()->create([
            'parent_type' => ChildSubMenu::class,
            'parent_id' => $childSubMenu->id,
            'layout_id' => $layout->id,
        ]);
        $this->actingAsSuperAdmin();
        $data = [
            'sub_menu_id' => $subMenu->id,
            'title' => 'Updated ChildSubMenu',
            'description' => 'Updated ChildSubMenu Description',
            'slug' => 'updated-childsubmenu',
            'is_active' => true,
            'order' => 1,
            'layout_id' => $layout->id,
            'type' => 'text',
            'content' => [
                'content' => 'Updated text',
            ],
        ];

        $response = $this->putJson("/api/child-sub-menu/{$childSubMenu->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);

        $this->assertDatabaseHas('child_sub_menus', [
            'id' => $childSubMenu->id,
            'description' => 'Updated ChildSubMenu Description',
        ]);
    }

    public function test_child_sub_menu_description_exceeding_max_fails_validation()
    {
        $menu = Menu::factory()->create();
        $subMenu = SubMenu::factory()->create(['menu_id' => $menu->id, 'has_child' => true]);
        $layout = MsLayout::first();
        $this->actingAsSuperAdmin();
        $data = [
            'sub_menu_id' => $subMenu->id,
            'title' => 'ChildSubMenu Test',
            'description' => str_repeat('a', 256),
            'slug' => 'childsubmenu-test-max',
            'order' => 1,
            'is_active' => true,
            'type' => 'text',
            'layout_id' => $layout->id,
        ];

        $response = $this->postJson('/api/child-sub-menu', $data);

        $response->assertStatus(422);
    }

    public function test_can_delete_child_sub_menu()
    {
        $menu = Menu::factory()->create();
        $subMenu = SubMenu::factory()->create(['menu_id' => $menu->id, 'has_child' => true]);
        $childSubMenu = ChildSubMenu::factory()->create(['sub_menu_id' => $subMenu->id]);
        $this->actingAsSuperAdmin();

        $response = $this->deleteJson("/api/child-sub-menu/{$childSubMenu->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('child_sub_menus', ['id' => $childSubMenu->id]);
    }

    public function test_returns_404_when_deleting_nonexistent_child_sub_menu()
    {
        $this->actingAsSuperAdmin();

        $response = $this->deleteJson('/api/child-sub-menu/99999');

        $response->assertStatus(404);
    }
}
