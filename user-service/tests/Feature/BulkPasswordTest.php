<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_endpoint_returns_success_envelope(): void
    {
        $response = $this->getJson('/api/bulk-password/template');
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }
}
