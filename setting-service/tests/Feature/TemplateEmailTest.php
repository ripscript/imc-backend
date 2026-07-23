<?php

namespace Tests\Feature;

use App\Models\TemplateEmail;
use App\Models\User;
use Tests\TestCase;

class TemplateEmailTest extends TestCase
{
    public function test_can_get_paginated_list()
    {
        TemplateEmail::factory()->count(3)->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/template-email');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_detail()
    {
        $template = TemplateEmail::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson("/api/template-email/{$template->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_template()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'admin')
            ->getJson('/api/template-email/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_template()
    {
        $template = TemplateEmail::factory()->create();
        $user = User::factory()->create();

        $data = [
            'subject' => 'Updated Subject',
            'body' => '<p>Updated content</p>',
        ];

        $response = $this->actingAs($user, 'admin')
            ->putJson("/api/template-email/{$template->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_update_all_templates()
    {
        $template1 = TemplateEmail::factory()->create();
        $template2 = TemplateEmail::factory()->create();
        $user = User::factory()->create();

        $data = [
            'data' => [
                ['id' => $template1->id, 'content' => 'New content 1', 'subject' => 'New subject 1'],
                ['id' => $template2->id, 'content' => 'New content 2', 'subject' => 'New subject 2'],
            ],
        ];

        $response = $this->actingAs($user, 'admin')
            ->putJson('/api/template-email/update-all', $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message']);
    }
}