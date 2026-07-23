<?php

namespace Tests\Feature;

use App\Models\QuickLink;
use Tests\TestCase;

class QuickLinkTest extends TestCase
{
    public function test_can_get_quick_links()
    {
        QuickLink::factory()->count(3)->create();

        $response = $this->getJson('/api/quick-link');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }
}