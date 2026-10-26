<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentDetailPublication;
use App\Models\ContentDetailText;
use App\Models\Menu;
use App\Models\SubMenu;
use Tests\TestCase;

class ContentIndexTest extends TestCase
{
    public function test_menu_with_publication_returns_pagination_meta()
    {
        $menu = Menu::factory()->create([
            'slug' => 'berita-dan-artikel',
            'has_sub' => false,
            'is_active' => true,
        ]);

        $content = Content::factory()->create([
            'parent_type' => Menu::class,
            'parent_id' => $menu->id,
            'type' => 'publication',
            'is_section' => false,
            'is_accessible' => true,
        ]);

        ContentDetailPublication::factory()->count(3)->create([
            'content_id' => $content->id,
            'is_publicated' => true,
        ]);

        $response = $this->getJson('/api/content?type=menu&slug[]=berita-dan-artikel');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.content_id', $content->id)
            ->assertJsonPath('data.title', $menu->title)
            ->assertJsonPath('data.slug', $menu->slug)
            ->assertJsonPath('data.is_section', false)
            ->assertJsonPath('data.is_accessible', true)
            ->assertJsonCount(3, 'data.content')
            ->assertJsonStructure([
                'data' => [
                    'meta' => ['total', 'page', 'limit', 'total_page'],
                    'breadcrumb',
                ],
            ]);
    }

    public function test_sub_menu_with_project_returns_two_level_breadcrumb()
    {
        $menu = Menu::factory()->create(['slug' => 'profil', 'has_sub' => true, 'is_active' => true]);
        $subMenu = SubMenu::factory()->create([
            'menu_id' => $menu->id,
            'slug' => 'proyek',
            'has_child' => false,
            'is_active' => true,
        ]);

        Content::factory()->create([
            'parent_type' => SubMenu::class,
            'parent_id' => $subMenu->id,
            'type' => 'project',
            'is_section' => false,
            'is_accessible' => true,
        ]);

        $response = $this->getJson('/api/content?type=sub_menu&slug[]=profil&slug[]=proyek');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'project')
            ->assertJsonPath('data.breadcrumb', [$menu->title, $subMenu->title]);
    }

    public function test_non_paginated_type_returns_null_meta()
    {
        $menu = Menu::factory()->create(['slug' => 'kontak-kami', 'has_sub' => false, 'is_active' => true]);

        $content = Content::factory()->create([
            'parent_type' => Menu::class,
            'parent_id' => $menu->id,
            'type' => 'text',
            'is_section' => false,
            'is_accessible' => true,
        ]);

        ContentDetailText::factory()->create([
            'content_id' => $content->id,
        ]);

        $response = $this->getJson('/api/content?type=menu&slug[]=kontak-kami');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.content_id', $content->id)
            ->assertJsonPath('data.meta', null)
            ->assertJsonStructure([
                'data' => [
                    'content',
                    'breadcrumb',
                ],
            ]);
    }

    public function test_inaccessible_content_returns_404()
    {
        $menu = Menu::factory()->create([
            'slug' => 'draft',
            'has_sub' => false,
            'is_active' => true,
        ]);

        Content::factory()->create([
            'parent_type' => Menu::class,
            'parent_id' => $menu->id,
            'type' => 'text',
            'is_accessible' => false,
        ]);

        $response = $this->getJson('/api/content?type=menu&slug[]=draft');

        $response->assertStatus(404);
    }
}