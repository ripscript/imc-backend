<?php

namespace Tests\Feature;

use Tests\TestCase;

class VisitorTest extends TestCase
{
    public function test_can_track_visitor()
    {
        $data = [
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 Test Browser',
        ];

        $response = $this->postJson('/api/visitor', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message']);
    }
}