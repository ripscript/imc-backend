<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestProjectCase;

class ProjectCreateTest extends TestProjectCase
{
    public function test_create_draft_returns_pic_email_and_attachments(): void
    {
        $clientId = DB::table('pm_clients')->insertGetId([
            'name' => 'PT ABC', 'identifier' => 'CL-9001', 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $categoryId = DB::table('pm_project_categories')->insertGetId([
            'code' => 'PC-9001', 'name' => 'Manufacturing', 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/projects', [
            'content_id' => $this->contentId,
            'title' => 'Development Mesin A',
            'client_id' => $clientId,
            'category_id' => $categoryId,
            'pic_user_id' => $this->admin->id,
            'target_end_date' => '2026-09-30',
            'budget' => 100000000,
            'description' => '<p>Deskripsi proyek</p>',
            'attachments' => [
                [
                    'file_url' => 'https://cdn.example.com/Rencana_Project_2026.pdf',
                    'file_name' => 'Rencana_Project_2026.pdf',
                    'file_size_kb' => 1228,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.pic.email', $this->admin->email)
            ->assertJsonPath('data.attachments.0.file_name', 'Rencana_Project_2026.pdf')
            ->assertJsonPath('data.attachments.0.file_size_kb', 1228);

        $this->assertMatchesRegularExpression('/^IMC\.\d{4}-\d+$/', $response->json('data.project_no'));
    }

    public function test_create_rejects_invalid_attachments(): void
    {
        $payload = [
            'content_id' => $this->contentId,
            'title' => 'Bad Attachment Project',
        ];

        // Disallowed extension.
        $this->postJson('/api/projects', array_merge($payload, [
            'attachments' => [['file_url' => 'https://cdn.example.com/malware.exe']],
        ]))->assertStatus(422);

        // Oversize file.
        $this->postJson('/api/projects', array_merge($payload, [
            'attachments' => [[
                'file_url' => 'https://cdn.example.com/big.pdf',
                'file_size_kb' => 99999,
            ]],
        ]))->assertStatus(422);

        // Too many files.
        $this->postJson('/api/projects', array_merge($payload, [
            'attachments' => array_fill(0, 6, 'https://cdn.example.com/a.pdf'),
        ]))->assertStatus(422);
    }

    public function test_update_is_partial_and_setup_state_tracks_step1(): void
    {
        $project = $this->createProject();

        $this->putJson("/api/projects/{$project['id']}", ['title' => 'Renamed Project'])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Renamed Project')
            ->assertJsonPath('data.client.name', 'PT Indofest Mentari');

        // Free navigation: updating one field keeps the rest intact.
        $this->assertDatabaseHas('content_detail_projects', [
            'project_no' => $project['project_no'],
            'title' => 'Renamed Project',
            'status' => 'draft',
        ]);

        $state = $this->getJson("/api/projects/{$project['id']}/setup-state")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.steps.1', true)
            ->assertJsonPath('data.steps.2', false)
            ->assertJsonPath('data.ready_to_activate', false);

        $this->assertContains('step_2.stages', $state->json('data.missing'));
    }

    public function test_legacy_project_no_key_is_rejected(): void
    {
        $project = $this->createProject();

        // Old project_no-style keys are numeric-constrained out of the routes.
        $this->getJson("/api/projects/{$project['project_no']}")
            ->assertStatus(404);
    }
}
