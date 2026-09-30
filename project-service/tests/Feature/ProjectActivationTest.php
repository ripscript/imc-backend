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

    public function test_activate_transitions_and_side_effects(): void
    {
        [$project, $stages] = $this->seedCompleteProject();

        $this->putJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}", ['requires_approval' => true])
            ->assertStatus(200);
        $this->putJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}/approvers", [
            'approvers' => [['approver_id' => $this->admin->id]],
        ])->assertStatus(200);

        $response = $this->postJson("/api/projects/{$project['id']}/activate");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('content_detail_projects', [
            'project_no' => $project['project_no'],
            'status' => 'active',
        ]);

        // First stage in progress.
        $this->assertSame('in_progress', DB::table('pm_project_stages')->where('id', $stages[0]['id'])->value('status'));
        $this->assertSame('pending', DB::table('pm_project_stages')->where('id', $stages[1]['id'])->value('status'));

        // First-stage approvals stamped as requested.
        $approval = DB::table('pm_approvals')->where('project_stage_id', $stages[0]['id'])->first();
        $this->assertNotNull($approval->requested_at);
        $this->assertSame($this->admin->id, (int) $approval->requested_by);

        // PIC synced into members.
        $this->assertDatabaseHas('pm_project_members', [
            'project_id' => $project['id'],
            'user_id' => $this->admin->id,
            'role' => 'pic',
            'status' => 'active',
        ]);

        // Review now reports the approval.
        $this->getJson("/api/projects/{$project['id']}/review")
            ->assertJsonPath('data.summary.total_approval', 1);
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
