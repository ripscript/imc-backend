<?php

namespace Tests\Feature;

use App\Models\PmApprovalRequest;
use App\Models\Project;
use App\Models\ProjectStage;
use Illuminate\Support\Facades\DB;
use Tests\TestProjectCase;

class ProjectActivationApprovalTest extends TestProjectCase
{
    public function test_activate_creates_pending_approval_request(): void
    {
        [$projectData] = $this->seedCompleteProject();
        $project = Project::findOrFail($projectData['id']);

        $response = $this->postJson("/api/projects/{$project->id}/activate");
        $response->assertStatus(200);

        $this->assertDatabaseHas('pm_approval_requests', [
            'project_id' => $project->id,
            'approvable_type' => 'project_activation',
            'approvable_id' => $project->id,
            'type' => 'activation',
            'status' => 'pending',
            'title' => $project->title,
            'requested_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('content_detail_projects', [
            'id' => $project->id,
            'status' => 'pending_approval',
            'activation_requested_by' => $this->admin->id,
            'rejection_notes' => null,
        ]);

        $approval = PmApprovalRequest::where('project_id', $project->id)
            ->where('type', 'activation')
            ->first();

        $this->assertNotNull($approval);
        $this->assertNotNull($approval->requested_at);
        $this->assertSame($this->admin->id, $approval->requested_by);
    }

    public function test_approve_activation_updates_approval_request_and_activates_project(): void
    {
        [$projectData] = $this->seedPendingProject();
        $project = Project::findOrFail($projectData['id']);

        $response = $this->postJson("/api/projects/{$project->id}/activate/approve");
        $response->assertStatus(200);

        $this->assertDatabaseHas('content_detail_projects', [
            'id' => $project->id,
            'status' => 'active',
            'activated_by' => $this->admin->id,
            'rejection_notes' => null,
        ]);

        $this->assertDatabaseHas('pm_approval_requests', [
            'project_id' => $project->id,
            'approvable_type' => 'project_activation',
            'approvable_id' => $project->id,
            'type' => 'activation',
            'status' => 'approved',
            'approved_by' => $this->admin->id,
        ]);

        $approval = PmApprovalRequest::where('project_id', $project->id)
            ->where('type', 'activation')
            ->first();

        $this->assertNotNull($approval);
        $this->assertNotNull($approval->approved_at);
    }

    public function test_reject_activation_requires_notes_and_updates_approval_request(): void
    {
        [$projectData] = $this->seedPendingProject();
        $project = Project::findOrFail($projectData['id']);

        // Empty notes rejected by validation
        $this->postJson("/api/projects/{$project->id}/activate/reject", [])
            ->assertStatus(422);

        // Valid reject with notes
        $response = $this->postJson("/api/projects/{$project->id}/activate/reject", [
            'notes' => 'RAB belum ditandatangani oleh pemohon.',
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('content_detail_projects', [
            'id' => $project->id,
            'status' => 'draft',
            'rejection_notes' => 'RAB belum ditandatangani oleh pemohon.',
        ]);

        $this->assertDatabaseHas('pm_approval_requests', [
            'project_id' => $project->id,
            'approvable_type' => 'project_activation',
            'approvable_id' => $project->id,
            'type' => 'activation',
            'status' => 'rejected',
            'rejection_notes' => 'RAB belum ditandatangani oleh pemohon.',
            'approved_by' => $this->admin->id,
        ]);
    }

    public function test_reactivate_after_rejection_clears_notes_and_creates_new_request(): void
    {
        [$projectData] = $this->seedPendingProject();
        $project = Project::findOrFail($projectData['id']);

        $this->postJson("/api/projects/{$project->id}/activate/reject", [
            'notes' => 'Perbaiki lampiran',
        ])->assertStatus(200);

        // Re-activate
        $this->postJson("/api/projects/{$project->id}/activate")->assertStatus(200);

        $this->assertDatabaseHas('content_detail_projects', [
            'id' => $project->id,
            'status' => 'pending_approval',
            'rejection_notes' => null,
        ]);

        // Historical requests preserved
        $requests = PmApprovalRequest::where('project_id', $project->id)
            ->where('type', 'activation')
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $requests);
        $this->assertSame('rejected', $requests[0]->status);
        $this->assertSame('Perbaiki lampiran', $requests[0]->rejection_notes);
        $this->assertSame('pending', $requests[1]->status);
        $this->assertNull($requests[1]->rejection_notes);
    }

    public function test_change_request_syncs_with_approval_requests(): void
    {
        [$projectData] = $this->seedActiveProject();
        $project = Project::findOrFail($projectData['id']);

        $crResponse = $this->postJson("/api/projects/{$project->id}/change-requests", [
            'title' => 'Perpanjang deadline',
            'reason' => 'Permintaan penambahan waktu dari client.',
            'proposed_changes' => ['target_end_date' => '2026-12-31'],
        ]);
        $crResponse->assertStatus(201);
        $crId = $crResponse->json('data.id');

        $this->assertDatabaseHas('pm_approval_requests', [
            'project_id' => $project->id,
            'approvable_type' => 'change_request',
            'approvable_id' => $crId,
            'type' => 'change_request',
            'status' => 'pending',
            'title' => $project->title . ' - Perpanjang deadline',
            'description' => 'Permintaan penambahan waktu dari client.',
            'requested_by' => $this->admin->id,
        ]);

        // Approve change request
        $this->postJson("/api/projects/{$project->id}/change-requests/{$crId}/approve")
            ->assertStatus(200);

        $this->assertDatabaseHas('pm_approval_requests', [
            'approvable_type' => 'change_request',
            'approvable_id' => $crId,
            'status' => 'approved',
            'approved_by' => $this->admin->id,
        ]);
    }

    public function test_change_request_reject_syncs_with_approval_requests(): void
    {
        [$projectData] = $this->seedActiveProject();
        $project = Project::findOrFail($projectData['id']);

        $crResponse = $this->postJson("/api/projects/{$project->id}/change-requests", [
            'title' => 'Tambah anggaran',
            'reason' => 'Biaya tambahan material.',
            'proposed_changes' => ['budget' => 999999999],
        ]);
        $crResponse->assertStatus(201);
        $crId = $crResponse->json('data.id');

        // Reject change request
        $this->postJson("/api/projects/{$project->id}/change-requests/{$crId}/reject", [
            'notes' => 'Anggaran melebihi batas plafon.',
        ])->assertStatus(200);

        $this->assertDatabaseHas('pm_approval_requests', [
            'approvable_type' => 'change_request',
            'approvable_id' => $crId,
            'status' => 'rejected',
            'rejection_notes' => 'Anggaran melebihi batas plafon.',
            'approved_by' => $this->admin->id,
        ]);
    }

    public function test_stage_clearance_re_request_does_not_overwrite_historical_rejected_requests(): void
    {
        [$projectData, $stagesData] = $this->seedActiveProject();
        $stageId = $stagesData[0]['id'];

        $this->putJson("/api/projects/{$projectData['id']}/stages/{$stageId}", [
            'requires_approval' => true,
        ])->assertStatus(200);

        // Request clearance
        $this->postJson("/api/projects/{$projectData['id']}/stages/{$stageId}/request-clearance", [
            'notes' => 'Mohon approval stage 1',
        ])->assertStatus(200);

        // Reject clearance
        $this->postJson("/api/projects/{$projectData['id']}/stages/{$stageId}/reject-clearance", [
            'notes' => 'Dokumen belum lengkap',
        ])->assertStatus(200);

        $stage = ProjectStage::findOrFail($stageId);
        $this->assertSame('Dokumen belum lengkap', $stage->rejection_notes);
        $this->assertSame('in_progress', $stage->status);

        // Re-request clearance
        $this->postJson("/api/projects/{$projectData['id']}/stages/{$stageId}/request-clearance", [
            'notes' => 'Dokumen sudah diperbaiki',
        ])->assertStatus(200);

        $stage->refresh();
        $this->assertNull($stage->rejection_notes);
        $this->assertSame('pending_clearance', $stage->status);

        $approvalRows = PmApprovalRequest::where('approvable_type', 'stage_clearance')
            ->where('approvable_id', $stageId)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $approvalRows);
        $this->assertSame('rejected', $approvalRows[0]->status);
        $this->assertSame('Dokumen belum lengkap', $approvalRows[0]->rejection_notes);
        $this->assertSame('pending', $approvalRows[1]->status);
        $this->assertNull($approvalRows[1]->rejection_notes);
    }
}
