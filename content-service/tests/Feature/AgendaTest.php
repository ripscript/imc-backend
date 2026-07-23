<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentDetailAgenda;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_side_menu()
    {
        Menu::factory()->create(['is_show_side_menu' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/agenda/side-menu');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_paginated_list_by_content()
    {
        $content = Content::factory()->create();
        ContentDetailAgenda::factory()->count(5)->create(['content_id' => $content->id]);
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/agenda/list/{$content->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data', 'meta']]);
    }

    public function test_can_create_agenda()
    {
        $user = User::factory()->create();
        $data = ContentDetailAgenda::factory()->make()->toArray();

        $response = $this->actingAs($user, 'admin')
            ->postJson('/api/agenda/', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_agenda_detail()
    {
        $agenda = ContentDetailAgenda::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/agenda/{$agenda->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_agenda()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/agenda/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_agenda()
    {
        $agenda = ContentDetailAgenda::factory()->create();
        $user = User::factory()->create();
        $data = ['title' => 'Updated Agenda Title'];

        $response = $this->actingAs($user, 'admin')
            ->putJson("/api/agenda/{$agenda->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_delete_agenda()
    {
        $agenda = ContentDetailAgenda::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson("/api/agenda/{$agenda->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_when_deleting_nonexistent_agenda()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->deleteJson('/api/agenda/99999');

        $response->assertStatus(404);
    }
}
