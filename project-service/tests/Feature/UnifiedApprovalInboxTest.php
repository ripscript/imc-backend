<?php

namespace Tests\Feature;

use App\Models\PmApprovalRequest;
use App\Models\PmStageApprover;
use App\Models\Project;
use App\Models\ProjectStage;
use Illuminate\Database\QueryException;
use Tests\TestProjectCase;

class UnifiedApprovalInboxTest extends TestProjectCase
{
    public function test_approval_requests_and_stage_approvers_schema(): void
    {
        $projectData = $this->createProject(['title' => 'Test Project']);
        $project = Project::findOrFail($projectData['id']);

        $approval = PmApprovalRequest::create([
            'project_id' => $project->id,
            'approvable_type' => 'project_activation',
            'approvable_id' => $project->id,
            'type' => 'activation',
            'title' => $project->title,
            'description' => 'Permintaan aktivasi proyek',
            'status' => 'pending',
            'requested_by' => $this->admin->id,
            'requested_at' => now(),
        ]);

        $this->assertDatabaseHas('pm_approval_requests', ['id' => $approval->id, 'type' => 'activation']);

        // Test project relation
        $this->assertTrue($project->approvalRequests->contains($approval));
        $this->assertSame($project->id, $approval->project->id);
        $this->assertInstanceOf(Project::class, $approval->approvable);
        $this->assertSame($project->id, $approval->approvable->id);
        $this->assertSame($this->admin->id, $approval->requester->id);
        $this->assertTrue($approval->isPending());
        $this->assertFalse($approval->isApproved());
        $this->assertFalse($approval->isRejected());

        // Test stage approver and relations
        $stage = ProjectStage::create([
            'project_id' => $project->id,
            'name' => 'Requirement',
            'sequence' => 1,
            'status' => 'pending',
            'clearance_requested_by' => $this->admin->id,
            'clearance_requested_at' => now(),
            'rejection_notes' => 'Please fix tasks',
        ]);

        $stageApprover = PmStageApprover::create([
            'project_stage_id' => $stage->id,
            'user_id' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('pm_stage_approvers', [
            'id' => $stageApprover->id,
            'project_stage_id' => $stage->id,
            'user_id' => $this->admin->id,
        ]);

        $this->assertTrue($stage->approvers->contains($stageApprover));
        $this->assertSame($stage->id, $stageApprover->stage->id);
        $this->assertSame($stage->id, $stageApprover->projectStage->id);
        $this->assertSame($this->admin->id, $stageApprover->user->id);
        $this->assertTrue($stage->approverUsers->contains($this->admin));

        // Test stage approval request relation
        $stageApproval = PmApprovalRequest::create([
            'project_id' => $project->id,
            'approvable_type' => 'stage_clearance',
            'approvable_id' => $stage->id,
            'type' => 'stage_clearance',
            'title' => 'Stage 1 Clearance',
            'status' => 'pending',
        ]);

        $this->assertTrue($stage->approvalRequests->contains($stageApproval));
        $this->assertInstanceOf(ProjectStage::class, $stageApproval->approvable);
        $this->assertSame($stage->id, $stageApproval->approvable->id);

        // Test project activation extra columns
        $project->update([
            'activation_requested_by' => $this->admin->id,
            'activation_requested_at' => now(),
            'rejection_notes' => 'Initial review notes',
        ]);

        $freshProject = $project->fresh();
        $this->assertSame($this->admin->id, $freshProject->activation_requested_by);
        $this->assertSame($this->admin->id, $freshProject->activationRequester->id);
        $this->assertNotNull($freshProject->activation_requested_at);
        $this->assertSame('Initial review notes', $freshProject->rejection_notes);

        // Test stage extra columns
        $freshStage = $stage->fresh();
        $this->assertSame($this->admin->id, $freshStage->clearance_requested_by);
        $this->assertSame($this->admin->id, $freshStage->clearanceRequester->id);
        $this->assertNotNull($freshStage->clearance_requested_at);
        $this->assertSame('Please fix tasks', $freshStage->rejection_notes);

        // Test soft deletes on PmApprovalRequest
        $stageApproval->delete();
        $this->assertSoftDeleted('pm_approval_requests', ['id' => $stageApproval->id]);
        $this->assertCount(1, PmApprovalRequest::all());
        $this->assertCount(2, PmApprovalRequest::withTrashed()->get());

        // Test unique constraint on pm_stage_approvers
        $this->expectException(QueryException::class);
        PmStageApprover::create([
            'project_stage_id' => $stage->id,
            'user_id' => $this->admin->id,
        ]);
    }
}
