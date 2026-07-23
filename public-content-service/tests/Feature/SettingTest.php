<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Tests\TestCase;

class SettingTest extends TestCase
{
    public function test_can_get_site_setting()
    {
        SiteSetting::factory()->create();

        $response = $this->getJson('/api/site-setting');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }
}