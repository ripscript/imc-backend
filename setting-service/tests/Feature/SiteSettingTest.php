<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    public function test_can_get_site_setting()
    {
        SiteSetting::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/site-setting');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_update_site_setting()
    {
        SiteSetting::factory()->create();
        $user = User::factory()->create();

        $data = [
            'address' => 'Jl. Test No. 123',
            'phone' => '021-1234567',
            'email' => 'test@example.com',
        ];

        $response = $this->actingAs($user, 'admin')
            ->putJson('/api/site-setting', $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }
}