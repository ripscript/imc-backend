<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerwakilanTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_returns_success_response(): void
    {
        $response = $this->getJson('/api/perwakilan');
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }
}
