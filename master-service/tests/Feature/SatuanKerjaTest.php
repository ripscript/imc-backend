<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SatuanKerjaTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_returns_success_response(): void
    {
        $response = $this->getJson('/api/satuan-kerja');
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }
}
