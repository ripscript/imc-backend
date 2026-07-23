<?php

namespace Tests\Feature;

use App\Models\ChatSetting;
use App\Models\User;
use Tests\TestCase;

class ChatSettingTest extends TestCase
{
    public function test_can_get_chat_setting()
    {
        ChatSetting::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/chat-setting');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_update_chat_setting()
    {
        ChatSetting::factory()->create();
        $user = User::factory()->create();

        $data = [
            'opening' => 'Halo, ada yang bisa dibantu?',
            'closing' => 'Terima kasih',
        ];

        $response = $this->actingAs($user, 'admin')
            ->putJson('/api/chat-setting', $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }
}