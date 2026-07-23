<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\LatestInformation;
use App\Models\Menu;
use Tests\TestCase;

class HomeTest extends TestCase
{
    public function test_can_get_home()
    {
        $menu = Menu::factory()->create(['slug' => 'beranda']);
        Content::factory()->create(['parent_type' => Menu::class, 'parent_id' => $menu->id]);

        $response = $this->getJson('/api/home');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_latest_information()
    {
        LatestInformation::factory()->count(3)->create();

        $response = $this->getJson('/api/home/latest-information');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_home()
    {
        $response = $this->getJson('/api/home/99999');

        $response->assertStatus(404);
    }
}