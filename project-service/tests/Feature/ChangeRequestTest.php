<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestProjectCase;

class ChangeRequestTest extends TestProjectCase
{
    protected function createChangeRequest(int $projectId, array $overrides = []): array
    {
        return $this->postJson("/api/projects/{$projectId}/change-requests", array_merge([
            'title' => 'Perpanjang target akhir',
            'reason' => 'Client meminta penambahan scope.',
            'proposed_changes' => ['target_end_date' => '2026-11-30', 'budget' => 750000000],
        ], $overrides))->assertStatus(201)->json('data');
    }

    public function test_create_records_pending_request_with_generated_number(): void
    {
        [$project] = $this->seedActiveProject();
        $clientId = $project['client']['id'];

        $cr = $this->postJson("/api/projects/{$project['id']}/change-requests", [
            'title' => 'Perpanjang target akhir',
            'reason' => 'Client meminta penambahan scope.',
            'client_id' => $clientId,
            'proposed_changes' => ['target_end_date' => '2026-11-30'],
        ])->assertStatus(201)->assertJsonPath('data.status', 'pending_approval')->json('data');

        $this->assertMatchesRegularExpression('/^CR-\d{4}-'.$project['id'].'-0001$/', $cr['change_no']);
        $this->assertSame($clientId, $cr['client']['id']);
        $this->assertSame($this->admin->id, $cr['requester']['id']);
        $this->assertSame('2026-11-30', $cr['proposed_changes']['target_end_date']);

        $this->assertDatabaseHas('pm_approval_requests', [
            'project_id' => $project['id'],
            'approvable_type' => 'change_request',
            'approvable_id' => $cr['id'],
            'type' => 'change_request',
            'status' => 'pending',
            'requested_by' => $this->admin->id,
        ]);
    }

    public function test_create_rejects_empty_or_unknown_changes(): void
    {
        [$project] = $this->seedActiveProject();

        // Missing payload → 422 from request validation.
        $this->postJson("/api/projects/{$project['id']}/change-requests", [
            'title' => 'Tanpa usulan',
        ])->assertStatus(422);

        // Unknown field → 422 from the whitelist.
        $this->postJson("/api/projects/{$project['id']}/change-requests", [
            'title' => 'Field asing',
            'proposed_changes' => ['hack_the_gibson' => true],
        ])->assertStatus(422);
    }

    public function test_approve_then_apply_writes_changes_to_project(): void
    {
        [$project, $stages] = $this->seedActiveProject();

        $cr = $this->createChangeRequest($project['id'], [
            'proposed_changes' => [
                'target_end_date' => '2026-11-30',
                'budget' => 750000000,
                'stages' => [['id' => $stages[0]['id'], 'start_date' => '2026-06-05']],
            ],
        ]);

        $this->postJson("/api/projects/{$project['id']}/change-requests/{$cr['id']}/approve")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('pm_approval_requests', [
            'project_id' => $project['id'],
            'approvable_type' => 'change_request',
            'approvable_id' => $cr['id'],
            'type' => 'change_request',
            'status' => 'approved',
            'approved_by' => $this->admin->id,
        ]);

        // Applying twice is a conflict; applying a pending CR is too.
        $pending = $this->createChangeRequest($project['id']);
        $this->postJson("/api/projects/{$project['id']}/change-requests/{$pending['id']}/apply")
            ->assertStatus(409);

        $this->postJson("/api/projects/{$project['id']}/change-requests/{$cr['id']}/apply")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'applied');

        $row = DB::table('content_detail_projects')->where('id', $project['id'])->first();
        $this->assertSame('2026-11-30', $row->target_end_date);
        $this->assertSame(750000000, (int) $row->budget);
        $this->assertSame('2026-06-05', DB::table('pm_project_stages')->where('id', $stages[0]['id'])->value('start_date'));

        $this->assertNotNull(DB::table('pm_change_requests')->where('id', $cr['id'])->value('applied_at'));

        // Approving an applied CR is a conflict.
        $this->postJson("/api/projects/{$project['id']}/change-requests/{$cr['id']}/approve")
            ->assertStatus(409);
    }

    public function test_reject_requires_notes_and_keeps_project_untouched(): void
    {
        [$project] = $this->seedActiveProject();

        $cr = $this->createChangeRequest($project['id']);

        $this->postJson("/api/projects/{$project['id']}/change-requests/{$cr['id']}/reject")
            ->assertStatus(422);

        $this->postJson("/api/projects/{$project['id']}/change-requests/{$cr['id']}/reject", [
            'notes' => 'Dampak biaya belum jelas.',
        ])->assertStatus(200)->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('pm_approval_requests', [
            'project_id' => $project['id'],
            'approvable_type' => 'change_request',
            'approvable_id' => $cr['id'],
            'type' => 'change_request',
            'status' => 'rejected',
            'rejection_notes' => 'Dampak biaya belum jelas.',
            'approved_by' => $this->admin->id,
        ]);

        $row = DB::table('pm_change_requests')->where('id', $cr['id'])->first();
        $this->assertSame('Dampak biaya belum jelas.', $row->notes);
        $this->assertSame($this->admin->id, (int) $row->approved_by);

        // Rejected CRs cannot be applied.
        $this->postJson("/api/projects/{$project['id']}/change-requests/{$cr['id']}/apply")
            ->assertStatus(409);

        // Project untouched.
        $this->assertSame('2026-09-30', DB::table('content_detail_projects')->where('id', $project['id'])->value('target_end_date'));
    }

    public function test_decisions_require_approver_role(): void
    {
        [$project] = $this->seedActiveProject();
        $cr = $this->createChangeRequest($project['id']);

        $this->loginAs($this->makeUser('admin_pustik'));

        $this->postJson("/api/projects/{$project['id']}/change-requests/{$cr['id']}/approve")->assertStatus(403);
        $this->postJson("/api/projects/{$project['id']}/change-requests/{$cr['id']}/reject", ['notes' => 'x'])->assertStatus(403);
        $this->postJson("/api/projects/{$project['id']}/change-requests/{$cr['id']}/apply")->assertStatus(403);

        // Recording + listing stay open to all project roles.
        $this->postJson("/api/projects/{$project['id']}/change-requests", [
            'title' => 'Usulan pustik',
            'proposed_changes' => ['budget' => 1],
        ])->assertStatus(201);
        $this->getJson("/api/projects/{$project['id']}/change-requests")->assertStatus(200);
    }

    public function test_list_filters_by_status(): void
    {
        [$project] = $this->seedActiveProject();

        $first = $this->createChangeRequest($project['id']);
        $this->createChangeRequest($project['id'], ['title' => 'Usulan kedua']);

        $this->postJson("/api/projects/{$project['id']}/change-requests/{$first['id']}/approve")->assertStatus(200);

        $this->getJson("/api/projects/{$project['id']}/change-requests?status=pending_approval")
            ->assertStatus(200)
            ->assertJsonPath('data.total', 1);

        $this->getJson("/api/projects/{$project['id']}/change-requests?status=approved")
            ->assertStatus(200)
            ->assertJsonPath('data.total', 1);
    }

    public function test_apply_rejects_foreign_stage(): void
    {
        [$project] = $this->seedActiveProject();
        [$other] = $this->seedActiveProject();

        $otherStageId = DB::table('pm_project_stages')->where('project_id', $other['id'])->value('id');

        $cr = $this->createChangeRequest($project['id'], [
            'proposed_changes' => ['stages' => [['id' => $otherStageId]]],
        ]);

        $this->postJson("/api/projects/{$project['id']}/change-requests/{$cr['id']}/approve")->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/change-requests/{$cr['id']}/apply")
            ->assertStatus(422);
    }
}
