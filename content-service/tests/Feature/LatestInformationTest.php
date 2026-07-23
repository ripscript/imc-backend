<?php

namespace Tests\Feature;

use App\Models\LatestInformation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LatestInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_paginated_list()
    {
        LatestInformation::factory()->count(5)->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/latest-information/');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data', 'meta']]);
    }

    public function test_can_create_latest_information()
    {
        $user = User::factory()->create();
        $data = LatestInformation::factory()->make()->toArray();

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/latest-information/', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_latest_information_detail()
    {
        $latestInformation = LatestInformation::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/latest-information/{$latestInformation->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_latest_information()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/latest-information/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_latest_information()
    {
        $latestInformation = LatestInformation::factory()->create();
        $user = User::factory()->create();
        $data = ['title' => 'Updated Title'];

        $response = $this->actingAs($user, 'admin')
            ->putJson("/api/latest-information/{$latestInformation->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_delete_latest_information()
    {
        $latestInformation = LatestInformation::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson("/api/latest-information/{$latestInformation->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_when_deleting_nonexistent_latest_information()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson('/api/latest-information/99999');

        $response->assertStatus(404);
    }
}
