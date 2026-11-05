<?php

namespace Tests\Feature;

use App\Models\ChildSubMenu;
use App\Models\Content;
use App\Models\Menu;
use App\Models\SubMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_menu_with_descriptions()
    {
        $layoutId = DB::table('ms_layout')->insertGetId([
            'title' => 'Text Style',
            'type' => 'text',
            'is_custom' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $menu = Menu::factory()->create([
            'title' => 'Menu 1',
            'description' => 'Menu 1 Description',
            'has_sub' => true,
            'is_active' => true,
        ]);

        $subMenu = SubMenu::factory()->create([
            'menu_id' => $menu->id,
            'title' => 'SubMenu 1',
            'description' => 'SubMenu 1 Description',
            'has_child' => true,
            'is_active' => true,
        ]);

        $childSubMenu = ChildSubMenu::factory()->create([
            'sub_menu_id' => $subMenu->id,
            'title' => 'ChildSubMenu 1',
            'description' => 'ChildSubMenu 1 Description',
            'is_active' => true,
        ]);

        Content::factory()->create([
            'parent_type' => ChildSubMenu::class,
            'parent_id' => $childSubMenu->id,
            'type' => 'text',
            'layout_id' => $layoutId,
        ]);

        $response = $this->getJson('/api/menu');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('Menu 1 Description', $data[0]['description']);
        $this->assertEquals('SubMenu 1 Description', $data[0]['sub_menu'][0]['description']);
        $this->assertEquals('ChildSubMenu 1 Description', $data[0]['sub_menu'][0]['child_sub_menu'][0]['description']);
    }
}
