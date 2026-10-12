<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestProjectCase;

class StageLifecycleTest extends TestProjectCase
{
    public function test_complete_blocked_by_open_tasks_then_succeeds(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $task = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/tasks", ['title' => 'Draft requirement'])
            ->assertStatus(201)
            ->json('data');

        $errors = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/complete")
            ->assertStatus(422)
            ->json('errors');
        $this->assertSame("Task 'Draft requirement' belum selesai.", $errors["stage_{$stageId}.open_tasks"][0]);

        $this->patchJson("/api/projects/{$project['id']}/tasks/{$task['id']}/toggle")->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/complete")
            ->assertStatus(200)
            ->assertJsonPath('data.stage.status', 'completed')
            ->assertJsonPath('data.project_status', 'active')
            ->assertJsonPath('data.next_stage.status', 'in_progress')
            ->assertJsonPath('data.next_stage.id', $stages[1]['id']);
    }

    public function test_complete_blocked_by_required_deliverable(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $deliverable = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/deliverables", [
            'name' => 'Production Plan',
            'is_required' => true,
        ])->assertStatus(201)->json('data');

        $errors = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/complete")
            ->assertStatus(422)
            ->json('errors');
        $this->assertSame("Deliverable 'Production Plan' belum selesai.", $errors["stage_{$stageId}.open_deliverables"][0]);

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}/deliverables/{$deliverable['id']}", [
            'status' => 'completed',
        ])->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/complete")->assertStatus(200);
    }

    public function test_complete_flagged_stage_requires_clearance_role(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", ['requires_approval' => true])
            ->assertStatus(200);

        // admin_pustik fills but cannot clear.
        $this->loginAs($this->makeUser('admin_pustik'));

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/complete")
            ->assertStatus(403);

        // super_admin clears: completed + decision recorded.
        $this->loginAs($this->admin);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/complete")
            ->assertStatus(200)
            ->assertJsonPath('data.stage.status', 'completed')
            ->assertJsonPath('data.next_stage.status', 'in_progress');

        $row = DB::table('pm_project_stages')->where('id', $stageId)->first();
        $this->assertSame($this->admin->id, (int) $row->approved_by);
        $this->assertNotNull($row->approved_at);
    }

    public function test_complete_unflagged_stage_by_non_approver(): void
    {
        [$project, $stages] = $this->seedActiveProject();

        $this->loginAs($this->makeUser('admin_pustik'));

        $this->postJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}/complete")
            ->assertStatus(200)
            ->assertJsonPath('data.stage.status', 'completed');

        $row = DB::table('pm_project_stages')->where('id', $stages[0]['id'])->first();
        $this->assertNull($row->approved_by);
    }

    public function test_complete_blocked_by_unsatisfied_dependency(): void
    {
        [$project, $stages] = $this->seedActiveProject();

        // Force the dependent stage into progress while its dependency is pending.
        DB::table('pm_project_stages')->where('id', $stages[0]['id'])->update(['status' => 'pending']);
        DB::table('pm_project_stages')->where('id', $stages[1]['id'])->update(['status' => 'in_progress']);

        $errors = $this->postJson("/api/projects/{$project['id']}/stages/{$stages[1]['id']}/complete")
            ->assertStatus(422)
            ->json('errors');
        $this->assertSame("Stage 'Requirement' belum selesai.", $errors["stage_{$stages[1]['id']}.dependency"][0]);
    }

    public function test_complete_requires_active_project_and_running_stage(): void
    {
        // Draft project (not activated yet).
        [$draft, $draftStages] = $this->seedCompleteProject();
        $this->postJson("/api/projects/{$draft['id']}/stages/{$draftStages[0]['id']}/complete")
            ->assertStatus(422)
            ->assertJsonPath('errors.project.0', 'Status proyek saat ini: draft.');

        // Active project but the target stage is not running.
        [$project, $stages] = $this->seedActiveProject();
        $this->postJson("/api/projects/{$project['id']}/stages/{$stages[1]['id']}/complete")
            ->assertStatus(422)
            ->assertJsonPath('errors.stage.0', 'Status stage saat ini: pending.');
    }

    public function test_completing_last_stage_completes_project(): void
    {
        [$project, $stages] = $this->seedActiveProject();

        $this->postJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}/complete")->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stages[1]['id']}/complete")
            ->assertStatus(200)
            ->assertJsonPath('data.project_status', 'completed')
            ->assertJsonPath('data.next_stage', null);

        $this->assertSame('completed', DB::table('content_detail_projects')->where('id', $project['id'])->value('status'));
    }

    public function test_skip_requires_allow_skip(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/skip")
            ->assertStatus(422)
            ->assertJsonPath('errors.stage.0', 'Aktifkan "Allow Skip" untuk melewati stage ini.');

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", ['allow_skip' => true])->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/skip")
            ->assertStatus(200)
            ->assertJsonPath('data.stage.status', 'skipped')
            ->assertJsonPath('data.next_stage.status', 'in_progress');
    }

    public function test_reopen_requires_completed_and_allow_repeat(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/complete")->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/reopen")
            ->assertStatus(422)
            ->assertJsonPath('errors.stage.0', 'Aktifkan "Allow Repeat" untuk membuka kembali stage ini.');

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", ['allow_repeat' => true])->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/reopen")
            ->assertStatus(200)
            ->assertJsonPath('data.stage.status', 'in_progress')
            ->assertJsonPath('data.stage.completed_at', null);

        // A running stage cannot be reopened again.
        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/reopen")->assertStatus(422);
    }

    public function test_stage_status_cannot_be_set_via_update_after_activation(): void
    {
        [$project, $stages] = $this->seedActiveProject();

        $this->putJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}", ['status' => 'completed'])
            ->assertStatus(422)
            ->assertJsonPath('errors.status.0', 'Gunakan endpoint complete/skip/reopen untuk mengubah status stage.');
    }

    public function test_complete_clears_pending_clearance_approval_request(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", ['requires_approval' => true])
            ->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/request-clearance")
            ->assertStatus(200);

        $this->assertDatabaseHas('pm_approval_requests', [
            'project_id' => $project['id'],
            'approvable_type' => 'stage_clearance',
            'approvable_id' => $stageId,
            'type' => 'stage_clearance',
            'status' => 'pending',
        ]);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/complete")
            ->assertStatus(200)
            ->assertJsonPath('data.stage.status', 'completed');

        $this->assertDatabaseHas('pm_approval_requests', [
            'project_id' => $project['id'],
            'approvable_type' => 'stage_clearance',
            'approvable_id' => $stageId,
            'type' => 'stage_clearance',
            'status' => 'approved',
            'approved_by' => $this->admin->id,
        ]);
    }
}
