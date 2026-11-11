<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentDetailRepository;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_repository_listing_returns_file_path()
    {
        $menu = Menu::factory()->create([
            'slug' => 'dokumen',
            'has_sub' => false,
            'is_active' => true,
        ]);

        $content = Content::factory()->create([
            'parent_type' => Menu::class,
            'parent_id' => $menu->id,
            'type' => 'repository',
            'is_section' => false,
            'is_accessible' => true,
        ]);

        $repo = ContentDetailRepository::factory()->create([
            'content_id' => $content->id,
            'title' => 'Dokumen Pedoman',
            'slug' => 'dokumen-pedoman',
            'file_path' => 'uploads/repository/pedoman.pdf',
            'is_publicated' => true,
        ]);

        $response = $this->getJson('/api/content?type=menu&slug[]=dokumen');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $items = $response->json('data.content');
        $this->assertNotEmpty($items);
        $this->assertArrayHasKey('file_path', (array) $items[0]);
        $this->assertEquals('uploads/repository/pedoman.pdf', ((object) $items[0])->file_path);
    }

    public function test_repository_detail_returns_file_path()
    {
        $menu = Menu::factory()->create([
            'slug' => 'dokumen',
            'has_sub' => false,
            'is_active' => true,
        ]);

        $content = Content::factory()->create([
            'parent_type' => Menu::class,
            'parent_id' => $menu->id,
            'type' => 'repository',
            'is_section' => false,
            'is_accessible' => true,
        ]);

        $repo = ContentDetailRepository::factory()->create([
            'content_id' => $content->id,
            'title' => 'Dokumen Pedoman',
            'slug' => 'dokumen-pedoman',
            'file_path' => 'uploads/repository/pedoman.pdf',
            'is_publicated' => true,
        ]);

        $response = $this->getJson('/api/content/repository?slug=dokumen-pedoman&type=menu&content_slug=dokumen');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.file_path', 'uploads/repository/pedoman.pdf');
    }
}
