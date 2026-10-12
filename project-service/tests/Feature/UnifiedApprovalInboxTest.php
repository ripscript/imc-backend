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

    public function test_approver_can_view_inbox_and_approve_directly(): void
    {
        $admin = $this->createAdminPusatUser();
        $project = $this->createPendingApprovalProject();
        $approval = PmApprovalRequest::where('project_id', $project->id)->first();

        $this->actingAs($admin, 'admin')
            ->getJson('/api/approvals')
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $approval->id)
            ->assertJsonPath('data.0.project_name', $project->title);

        $this->actingAs($admin, 'admin')
            ->postJson("/api/approvals/{$approval->id}/approve")
            ->assertStatus(200);

        $this->assertDatabaseHas('pm_approval_requests', [
            'id' => $approval->id,
            'status' => 'approved',
        ]);
        $this->assertEquals('active', $project->fresh()->status);
    }

    public function test_stage_approver_only_sees_assigned_stages_if_not_admin_pusat(): void
    {
        $userA = $this->createUser();
        $userB = $this->createUser();
        $stageA = $this->createStageWithApprover($userA);
        $stageB = $this->createStageWithApprover($userB);

        $this->actingAs($userA, 'admin')
            ->getJson('/api/approvals')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.approvable_id', $stageA->id);
    }

    public function test_approver_can_filter_by_status_and_type_and_search(): void
    {
        $admin = $this->createAdminPusatUser();
        $project = $this->createPendingApprovalProject(['title' => 'Alpha Special Project']);
        $approvalActivation = PmApprovalRequest::where('project_id', $project->id)->first();

        $user = $this->createUser();
        $stage = $this->createStageWithApprover($user);
        $approvalStage = PmApprovalRequest::where('approvable_id', $stage->id)->first();

        // 1. Filter type=activation
        $response = $this->actingAs($admin, 'admin')
            ->getJson('/api/approvals?type=activation')
            ->assertStatus(200);
        $items = $response->json('data');
        $this->assertNotEmpty($items);
        foreach ($items as $item) {
            $this->assertEquals('activation', $item['request_type']);
        }

        // 2. Filter type=stage_clearance
        $response = $this->actingAs($admin, 'admin')
            ->getJson('/api/approvals?type=stage_clearance')
            ->assertStatus(200);
        $items = $response->json('data');
        $this->assertNotEmpty($items);
        foreach ($items as $item) {
            $this->assertEquals('stage_clearance', $item['request_type']);
        }

        // 3. Search by project title
        $response = $this->actingAs($admin, 'admin')
            ->getJson('/api/approvals?search=Alpha Special')
            ->assertStatus(200)
            ->assertJsonPath('data.0.project_name', 'Alpha Special Project');

        // 4. Status filter: approve one, check filter status=approved vs status=pending
        $this->actingAs($admin, 'admin')
            ->postJson("/api/approvals/{$approvalActivation->id}/approve")
            ->assertStatus(200);

        $approvedResponse = $this->actingAs($admin, 'admin')
            ->getJson('/api/approvals?status=approved')
            ->assertStatus(200);
        $approvedIds = collect($approvedResponse->json('data'))->pluck('id')->all();
        $this->assertContains($approvalActivation->id, $approvedIds);

        $pendingResponse = $this->actingAs($admin, 'admin')
            ->getJson('/api/approvals?status=pending')
            ->assertStatus(200);
        $pendingIds = collect($pendingResponse->json('data'))->pluck('id')->all();
        $this->assertNotContains($approvalActivation->id, $pendingIds);
        $this->assertContains($approvalStage->id, $pendingIds);
    }

    public function test_direct_reject_from_inbox_with_notes(): void
    {
        $admin = $this->createAdminPusatUser();
        $project = $this->createPendingApprovalProject();
        $approval = PmApprovalRequest::where('project_id', $project->id)->first();

        // 1. Missing notes -> 422
        $this->actingAs($admin, 'admin')
            ->postJson("/api/approvals/{$approval->id}/reject", [])
            ->assertStatus(422);

        // 2. Reject with valid notes
        $notes = 'Dokumen TOR belum lengkap dan budget belum disetujui.';
        $this->actingAs($admin, 'admin')
            ->postJson("/api/approvals/{$approval->id}/reject", ['notes' => $notes])
            ->assertStatus(200);

        $this->assertDatabaseHas('pm_approval_requests', [
            'id' => $approval->id,
            'status' => 'rejected',
            'rejection_notes' => $notes,
        ]);

        $this->assertEquals('draft', $project->fresh()->status);
        $this->assertEquals($notes, $project->fresh()->rejection_notes);
    }

    public function test_direct_approve_and_reject_stage_clearance_and_change_request(): void
    {
        $admin = $this->createAdminPusatUser();
        $user = $this->createUser();

        // Stage clearance approve
        $stage = $this->createStageWithApprover($user);
        $stageApproval = PmApprovalRequest::where('approvable_type', 'stage_clearance')
            ->where('approvable_id', $stage->id)
            ->first();

        $this->actingAs($user, 'admin')
            ->postJson("/api/approvals/{$stageApproval->id}/approve")
            ->assertStatus(200);

        $this->assertEquals('completed', $stage->fresh()->status);
        $this->assertEquals('approved', $stageApproval->fresh()->status);

        // Stage clearance reject
        $stage2 = $this->createStageWithApprover($user);
        $stageApproval2 = PmApprovalRequest::where('approvable_type', 'stage_clearance')
            ->where('approvable_id', $stage2->id)
            ->first();

        $this->actingAs($user, 'admin')
            ->postJson("/api/approvals/{$stageApproval2->id}/reject", ['notes' => 'Tasks still incomplete'])
            ->assertStatus(200);

        $this->assertEquals('in_progress', $stage2->fresh()->status);
        $this->assertEquals('Tasks still incomplete', $stage2->fresh()->rejection_notes);
        $this->assertEquals('rejected', $stageApproval2->fresh()->status);

        // Change request approve and reject
        [$activeProjData] = $this->seedActiveProject();
        $activeProject = Project::findOrFail($activeProjData['id']);

        $cr1 = \App\Models\PmChangeRequest::create([
            'project_id' => $activeProject->id,
            'change_no' => 'CR-2026-TEST-0001',
            'title' => 'Timeline Extension',
            'proposed_changes' => ['duration' => '6 months'],
            'status' => 'pending_approval',
            'requested_by' => $admin->id,
        ]);
        $crApproval1 = PmApprovalRequest::create([
            'project_id' => $activeProject->id,
            'approvable_type' => 'change_request',
            'approvable_id' => $cr1->id,
            'type' => 'change_request',
            'title' => $activeProject->title.' - '.$cr1->title,
            'status' => 'pending',
            'requested_by' => $admin->id,
            'requested_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->postJson("/api/approvals/{$crApproval1->id}/approve")
            ->assertStatus(200);

        $this->assertEquals('approved', $cr1->fresh()->status);
        $this->assertEquals('approved', $crApproval1->fresh()->status);

        $cr2 = \App\Models\PmChangeRequest::create([
            'project_id' => $activeProject->id,
            'change_no' => 'CR-2026-TEST-0002',
            'title' => 'Budget Increase',
            'proposed_changes' => ['budget' => 50000000],
            'status' => 'pending_approval',
            'requested_by' => $admin->id,
        ]);
        $crApproval2 = PmApprovalRequest::create([
            'project_id' => $activeProject->id,
            'approvable_type' => 'change_request',
            'approvable_id' => $cr2->id,
            'type' => 'change_request',
            'title' => $activeProject->title.' - '.$cr2->title,
            'status' => 'pending',
            'requested_by' => $admin->id,
            'requested_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->postJson("/api/approvals/{$crApproval2->id}/reject", ['notes' => 'Budget exceeds limit'])
            ->assertStatus(200);

        $this->assertEquals('rejected', $cr2->fresh()->status);
        $this->assertEquals('Budget exceeds limit', $cr2->fresh()->notes);
        $this->assertEquals('rejected', $crApproval2->fresh()->status);
    }

    public function test_conflict_409_when_already_decided(): void
    {
        $admin = $this->createAdminPusatUser();
        $project = $this->createPendingApprovalProject();
        $approval = PmApprovalRequest::where('project_id', $project->id)->first();

        // First approve succeeds
        $this->actingAs($admin, 'admin')
            ->postJson("/api/approvals/{$approval->id}/approve")
            ->assertStatus(200);

        // Second approve fails with 409 Conflict
        $this->actingAs($admin, 'admin')
            ->postJson("/api/approvals/{$approval->id}/approve")
            ->assertStatus(409);

        // Reject also fails with 409 Conflict
        $this->actingAs($admin, 'admin')
            ->postJson("/api/approvals/{$approval->id}/reject", ['notes' => 'Cannot reject approved'])
            ->assertStatus(409);
    }

    public function test_forbidden_403_when_unauthorized_user_tries_to_approve_or_reject(): void
    {
        $userA = $this->createUser();
        $userB = $this->createUser();

        // 1. Activation cannot be approved/rejected by non-central admin
        $project = $this->createPendingApprovalProject();
        $activationApproval = PmApprovalRequest::where('project_id', $project->id)->first();

        $this->actingAs($userA, 'admin')
            ->postJson("/api/approvals/{$activationApproval->id}/approve")
            ->assertStatus(403);

        $this->actingAs($userA, 'admin')
            ->postJson("/api/approvals/{$activationApproval->id}/reject", ['notes' => 'Not authorized'])
            ->assertStatus(403);

        // 2. Stage clearance cannot be approved/rejected by unassigned user
        $stageA = $this->createStageWithApprover($userA);
        $stageApprovalA = PmApprovalRequest::where('approvable_type', 'stage_clearance')
            ->where('approvable_id', $stageA->id)
            ->first();

        $this->actingAs($userB, 'admin')
            ->postJson("/api/approvals/{$stageApprovalA->id}/approve")
            ->assertStatus(403);

        $this->actingAs($userB, 'admin')
            ->postJson("/api/approvals/{$stageApprovalA->id}/reject", ['notes' => 'Not my stage'])
            ->assertStatus(403);
    }

    public function test_type_all_query_parameter_returns_all_types(): void
    {
        $admin = $this->createAdminPusatUser();
        $this->createPendingApprovalProject(['title' => 'Project Alpha']);
        $user = $this->createUser();
        $this->createStageWithApprover($user);

        $response = $this->actingAs($admin, 'admin')
            ->getJson('/api/approvals?type=all')
            ->assertStatus(200);

        $types = collect($response->json('data'))->pluck('request_type')->unique()->values()->all();
        $this->assertContains('activation', $types);
        $this->assertContains('stage_clearance', $types);
    }

    public function test_per_page_parameter_is_clamped_between_1_and_100(): void
    {
        $admin = $this->createAdminPusatUser();
        $this->createPendingApprovalProject(['title' => 'Project Alpha']);

        // Test over limit: 500 should be capped to 100
        $responseMax = $this->actingAs($admin, 'admin')
            ->getJson('/api/approvals?per_page=500')
            ->assertStatus(200);
        $this->assertEquals(100, $responseMax->json('meta.per_page'));

        // Test below limit: 0 should be floored to 1
        $responseMin = $this->actingAs($admin, 'admin')
            ->getJson('/api/approvals?per_page=0')
            ->assertStatus(200);
        $this->assertEquals(1, $responseMin->json('meta.per_page'));
    }

    public function test_search_wildcards_are_sanitized(): void
    {
        $admin = $this->createAdminPusatUser();
        $this->createPendingApprovalProject(['title' => 'Alpha Normal Project']);

        // Normal search matches
        $responseNormal = $this->actingAs($admin, 'admin')
            ->getJson('/api/approvals?search=Alpha')
            ->assertStatus(200);
        $this->assertCount(1, $responseNormal->json('data'));

        // Wildcard '%' search is sanitized so it does not match as wildcard
        $responsePercent = $this->actingAs($admin, 'admin')
            ->getJson('/api/approvals?search=%')
            ->assertStatus(200);
        $this->assertCount(0, $responsePercent->json('data'));

        // Wildcard '_' search is sanitized so it does not match as wildcard
        $responseUnderscore = $this->actingAs($admin, 'admin')
            ->getJson('/api/approvals?search=_____')
            ->assertStatus(200);
        $this->assertCount(0, $responseUnderscore->json('data'));
    }

    public function test_caller_model_is_not_mutated_with_dynamic_assigned_stage_ids(): void
    {
        $userA = $this->createUser();
        $this->createStageWithApprover($userA);

        $this->actingAs($userA, 'admin')
            ->getJson('/api/approvals')
            ->assertStatus(200);

        // Verify that Eloquent attributes or properties on userA were not mutated
        $this->assertFalse(array_key_exists('assigned_stage_ids', $userA->getAttributes()));
        $this->assertFalse(isset($userA->assigned_stage_ids));
    }
}
