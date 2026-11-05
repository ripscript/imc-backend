<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\LatestInformation;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_home(): void
    {
        $menu = Menu::factory()->create(['slug' => 'beranda']);
        Content::factory()->create([
            'parent_type' => Menu::class,
            'parent_id' => $menu->id,
            'is_section' => true,
            'section_title' => 'Layanan Unggulan',
            'section_description' => 'Deskripsi Layanan Unggulan',
        ]);

        $response = $this->getJson('/api/home');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('Layanan Unggulan', $data[0]['section_title']);
        $this->assertEquals('Deskripsi Layanan Unggulan', $data[0]['section_description']);
    }

    public function test_can_get_section_detail_with_description(): void
    {
        $menu = Menu::factory()->create(['slug' => 'beranda']);
        $content = Content::factory()->create([
            'parent_type' => Menu::class,
            'parent_id' => $menu->id,
            'is_section' => true,
            'type' => 'card_link',
            'section_title' => 'Layanan Unggulan',
            'section_description' => 'Deskripsi Layanan Unggulan',
        ]);

        $contentHash = md5($content->id);
        $response = $this->getJson("/api/home/{$contentHash}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);

        $data = $response->json('data');
        $this->assertEquals('Layanan Unggulan', $data['section_title']);
        $this->assertEquals('Deskripsi Layanan Unggulan', $data['section_description']);
    }

    public function test_can_get_latest_information(): void
    {
        LatestInformation::factory()->count(3)->create();

        $response = $this->getJson('/api/home/latest-information');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_home(): void
    {
        $response = $this->getJson('/api/home/99999');

        $response->assertStatus(404);
    }
}
