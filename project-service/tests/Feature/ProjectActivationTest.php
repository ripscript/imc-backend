<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestProjectCase;

class ProjectActivationTest extends TestProjectCase
{
    public function test_review_returns_summary_and_timeline_preview(): void
    {
        [$project, $stages] = $this->seedCompleteProject();

        $this->postJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}/deliverables", [
            'name' => 'Production Plan',
        ])->assertStatus(201);

        $response = $this->getJson("/api/projects/{$project['id']}/review");

        $response->assertStatus(200)->assertJsonPath('success', true);

        $summary = $response->json('data.summary');
        $this->assertSame($project['project_no'], $summary['project_no']);
        $this->assertSame('Development Mesin Giling', $summary['name']);
        $this->assertSame('PT Indofest Mentari', $summary['client']['name']);
        $this->assertSame($this->admin->name, $summary['pic']['name']);
        $this->assertSame(2, $summary['total_stage']);
        $this->assertSame('2026-06-01', $summary['period']['start_date']);
        $this->assertSame('2026-09-30', $summary['period']['target_end_date']);
        $this->assertSame(122, $summary['total_duration_days']);
        $this->assertSame(1, $summary['dokumen_wajib']);
        $this->assertSame(1, $summary['total_pic']);
        $this->assertSame(0, $summary['total_approval']);

        $preview = $response->json('data.timeline_preview');
        $this->assertCount(2, $preview);
        $this->assertSame(['no' => 1, 'name' => 'Requirement', 'start_date' => '2026-06-03', 'end_date' => '2026-06-09'], $preview[0]);

        $this->assertTrue($response->json('data.setup.ready_to_activate'));
    }

    public function test_activate_rejects_incomplete_project(): void
    {
        $project = $this->createProject();

        $response = $this->postJson("/api/projects/{$project['id']}/activate");

        $response->assertStatus(422);
        $this->assertContains('step_2.stages', $response->json('errors'));
    }

    public function test_activate_submits_for_approval_then_approves(): void
    {
        [$project, $stages] = $this->seedCompleteProject();

        $response = $this->postJson("/api/projects/{$project['id']}/activate");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending_approval');

        $this->assertDatabaseHas('content_detail_projects', [
            'project_no' => $project['project_no'],
            'status' => 'pending_approval',
        ]);

        // Stages wait; nothing starts before approval.
        $this->assertSame('pending', DB::table('pm_project_stages')->where('id', $stages[0]['id'])->value('status'));

        // PIC synced into members at submission.
        $this->assertDatabaseHas('pm_project_members', [
            'project_id' => $project['id'],
            'user_id' => $this->admin->id,
            'role' => 'pic',
            'status' => 'active',
        ]);

        // Approval goes live: first stage starts, decision recorded.
        $this->postJson("/api/projects/{$project['id']}/activate/approve")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'active');

        $this->assertSame('active', DB::table('content_detail_projects')->where('id', $project['id'])->value('status'));
        $this->assertSame('in_progress', DB::table('pm_project_stages')->where('id', $stages[0]['id'])->value('status'));
        $this->assertSame('pending', DB::table('pm_project_stages')->where('id', $stages[1]['id'])->value('status'));
        $this->assertSame($this->admin->id, (int) DB::table('content_detail_projects')->where('id', $project['id'])->value('activated_by'));
        $this->assertNotNull(DB::table('content_detail_projects')->where('id', $project['id'])->value('activated_at'));
    }

    public function test_reject_activation_reverts_to_draft(): void
    {
        [$project] = $this->seedPendingProject();

        $this->postJson("/api/projects/{$project['id']}/activate/reject")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'draft');

        $this->assertSame('draft', DB::table('content_detail_projects')->where('id', $project['id'])->value('status'));
    }

    public function test_activation_decision_requires_approver_role(): void
    {
        [$project] = $this->seedPendingProject();

        $this->loginAs($this->makeUser('admin_pustik'));

        $this->postJson("/api/projects/{$project['id']}/activate/approve")->assertStatus(403);
        $this->postJson("/api/projects/{$project['id']}/activate/reject")->assertStatus(403);

        // Still pending — the decisions were refused.
        $this->assertSame('pending_approval', DB::table('content_detail_projects')->where('id', $project['id'])->value('status'));
    }

    public function test_activation_decision_on_wrong_status_returns_409(): void
    {
        [$project] = $this->seedCompleteProject();

        // Still draft — nothing to decide yet.
        $this->postJson("/api/projects/{$project['id']}/activate/approve")->assertStatus(409);
        $this->postJson("/api/projects/{$project['id']}/activate/reject")->assertStatus(409);

        $this->postJson("/api/projects/{$project['id']}/activate")->assertStatus(200);
        $this->postJson("/api/projects/{$project['id']}/activate/approve")->assertStatus(200);

        // Already active — deciding again is a conflict.
        $this->postJson("/api/projects/{$project['id']}/activate/approve")->assertStatus(409);
    }

    public function test_activate_twice_returns_409(): void
    {
        [$project] = $this->seedCompleteProject();

        $this->postJson("/api/projects/{$project['id']}/activate")->assertStatus(200);
        $this->postJson("/api/projects/{$project['id']}/activate")->assertStatus(409);
    }

    public function test_save_draft_keeps_project_as_draft(): void
    {
        [$project] = $this->seedCompleteProject();

        $this->putJson("/api/projects/{$project['id']}", ['title' => 'Still Draft'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('content_detail_projects', [
            'project_no' => $project['project_no'],
            'status' => 'draft',
        ]);
    }
}
