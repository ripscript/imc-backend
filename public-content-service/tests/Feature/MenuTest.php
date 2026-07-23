<?php

namespace Tests\Feature;

use App\Models\Menu;
use Tests\TestCase;

class MenuTest extends TestCase
{
    public function test_can_get_menu()
    {
        Menu::factory()->count(3)->create();

        $response = $this->getJson('/api/menu');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }
}