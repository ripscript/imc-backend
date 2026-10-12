<?php

namespace Tests\Feature;

use Tests\TestProjectCase;

class StageClearanceLifecycleTest extends TestProjectCase
{
    public function test_stage_configuration_supports_approver_user_ids(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $approver1 = $this->makeUser('admin_pustik');
        $approver2 = $this->makeUser('admin_pustik');

        $response = $this->putJson("/api/projects/{$project['id']}/stages/configuration", [
            'stages' => [
                [
                    'id' => $stageId,
                    'requires_approval' => true,
                    'approver_user_ids' => [$approver1->id, $approver2->id],
                ],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('pm_stage_approvers', [
            'project_stage_id' => $stageId,
            'user_id' => $approver1->id,
        ]);
        $this->assertDatabaseHas('pm_stage_approvers', [
            'project_stage_id' => $stageId,
            'user_id' => $approver2->id,
        ]);

        // Sync again with only approver2
        $this->putJson("/api/projects/{$project['id']}/stages/configuration", [
            'stages' => [
                [
                    'id' => $stageId,
                    'requires_approval' => true,
                    'approver_user_ids' => [$approver2->id],
                ],
            ],
        ])->assertStatus(200);

        $this->assertDatabaseMissing('pm_stage_approvers', [
            'project_stage_id' => $stageId,
            'user_id' => $approver1->id,
        ]);
        $this->assertDatabaseHas('pm_stage_approvers', [
            'project_stage_id' => $stageId,
            'user_id' => $approver2->id,
        ]);
    }

    public function test_cannot_request_clearance_if_tasks_incomplete(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", [
            'requires_approval' => true,
        ])->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/tasks", [
            'title' => 'Open Task 1',
        ])->assertStatus(201);

        $response = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/request-clearance");
        $response->assertStatus(422)
            ->assertJsonValidationErrors(["stage_{$stageId}.open_tasks"]);
    }

    public function test_cannot_request_clearance_if_required_deliverables_incomplete(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", [
            'requires_approval' => true,
        ])->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/deliverables", [
            'name' => 'Required Deliverable',
            'is_required' => true,
        ])->assertStatus(201);

        $response = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/request-clearance");
        $response->assertStatus(422)
            ->assertJsonValidationErrors(["stage_{$stageId}.open_deliverables"]);
    }

    public function test_cannot_request_clearance_if_requires_approval_is_false(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", [
            'requires_approval' => false,
        ])->assertStatus(200);

        $response = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/request-clearance");
        $response->assertStatus(422);
    }

    public function test_can_request_clearance_when_all_done(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", [
            'requires_approval' => true,
        ])->assertStatus(200);

        $task = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/tasks", [
            'title' => 'Done Task',
        ])->assertStatus(201)->json('data');
        $this->patchJson("/api/projects/{$project['id']}/tasks/{$task['id']}/toggle")->assertStatus(200);

        $deliverable = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/deliverables", [
            'name' => 'Completed Deliverable',
            'is_required' => true,
        ])->assertStatus(201)->json('data');
        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}/deliverables/{$deliverable['id']}", [
            'status' => 'completed',
        ])->assertStatus(200);

        $response = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/request-clearance", [
            'notes' => 'Siap review clearance',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.stage.status', 'pending_clearance');

        $this->assertDatabaseHas('pm_project_stages', [
            'id' => $stageId,
            'status' => 'pending_clearance',
            'clearance_requested_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('pm_approval_requests', [
            'project_id' => $project['id'],
            'approvable_type' => 'stage_clearance',
            'approvable_id' => $stageId,
            'type' => 'stage_clearance',
            'status' => 'pending',
            'requested_by' => $this->admin->id,
            'description' => 'Siap review clearance',
        ]);
    }

    public function test_assigned_stage_approver_can_approve_clearance(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $approver = $this->makeUser('admin_pustik');

        // Configure stage with approver
        $this->putJson("/api/projects/{$project['id']}/stages/configuration", [
            'stages' => [
                [
                    'id' => $stageId,
                    'requires_approval' => true,
                    'approver_user_ids' => [$approver->id],
                ],
            ],
        ])->assertStatus(200);

        // Request clearance
        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/request-clearance", [
            'notes' => 'Tolong dicek',
        ])->assertStatus(200);

        // Login as assigned approver (admin_pustik) and approve/complete
        $this->loginAs($approver);

        $response = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/complete");
        $response->assertStatus(200)
            ->assertJsonPath('data.stage.status', 'completed')
            ->assertJsonPath('data.next_stage.status', 'in_progress');

        $this->assertDatabaseHas('pm_project_stages', [
            'id' => $stageId,
            'status' => 'completed',
            'approved_by' => $approver->id,
        ]);

        $this->assertDatabaseHas('pm_approval_requests', [
            'approvable_type' => 'stage_clearance',
            'approvable_id' => $stageId,
            'status' => 'approved',
            'approved_by' => $approver->id,
        ]);
    }

    public function test_central_admin_can_approve_clearance_even_if_not_assigned(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $assignedApprover = $this->makeUser('admin_pustik');

        // Assign only the admin_pustik
        $this->putJson("/api/projects/{$project['id']}/stages/configuration", [
            'stages' => [
                [
                    'id' => $stageId,
                    'requires_approval' => true,
                    'approver_user_ids' => [$assignedApprover->id],
                ],
            ],
        ])->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/request-clearance")
            ->assertStatus(200);

        // Login as admin_pusat (central admin override)
        $centralAdmin = $this->makeUser('admin_pusat');
        $this->loginAs($centralAdmin);

        $response = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/complete");
        $response->assertStatus(200)
            ->assertJsonPath('data.stage.status', 'completed');

        $this->assertDatabaseHas('pm_approval_requests', [
            'approvable_type' => 'stage_clearance',
            'approvable_id' => $stageId,
            'status' => 'approved',
            'approved_by' => $centralAdmin->id,
        ]);
    }

    public function test_non_approver_cannot_complete_flagged_stage(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $assignedApprover = $this->makeUser('admin_pustik');
        $nonApprover = $this->makeUser('admin_pustik');

        $this->putJson("/api/projects/{$project['id']}/stages/configuration", [
            'stages' => [
                [
                    'id' => $stageId,
                    'requires_approval' => true,
                    'approver_user_ids' => [$assignedApprover->id],
                ],
            ],
        ])->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/request-clearance")
            ->assertStatus(200);

        // Login as non-assigned admin_pustik
        $this->loginAs($nonApprover);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/complete")
            ->assertStatus(403);
    }

    public function test_reject_clearance_with_notes_reverts_to_in_progress(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $approver = $this->makeUser('admin_pustik');

        $this->putJson("/api/projects/{$project['id']}/stages/configuration", [
            'stages' => [
                [
                    'id' => $stageId,
                    'requires_approval' => true,
                    'approver_user_ids' => [$approver->id],
                ],
            ],
        ])->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/request-clearance", [
            'notes' => 'Siap review',
        ])->assertStatus(200);

        // Reject as approver
        $this->loginAs($approver);

        $response = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/reject-clearance", [
            'notes' => 'Dokumen QA belum lengkap',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.stage.status', 'in_progress');

        $this->assertDatabaseHas('pm_project_stages', [
            'id' => $stageId,
            'status' => 'in_progress',
            'rejection_notes' => 'Dokumen QA belum lengkap',
        ]);

        $this->assertDatabaseHas('pm_approval_requests', [
            'approvable_type' => 'stage_clearance',
            'approvable_id' => $stageId,
            'status' => 'rejected',
            'rejection_notes' => 'Dokumen QA belum lengkap',
            'approved_by' => $approver->id,
        ]);
    }

    public function test_reject_clearance_without_notes_fails(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", [
            'requires_approval' => true,
        ])->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/request-clearance")
            ->assertStatus(200);

        $response = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/reject-clearance", []);
        $response->assertStatus(422);

        $responseEmpty = $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/reject-clearance", [
            'notes' => '',
        ]);
        $responseEmpty->assertStatus(422);
    }

    public function test_non_approver_cannot_reject_clearance(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $assignedApprover = $this->makeUser('admin_pustik');
        $nonApprover = $this->makeUser('admin_pustik');

        $this->putJson("/api/projects/{$project['id']}/stages/configuration", [
            'stages' => [
                [
                    'id' => $stageId,
                    'requires_approval' => true,
                    'approver_user_ids' => [$assignedApprover->id],
                ],
            ],
        ])->assertStatus(200);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/request-clearance")
            ->assertStatus(200);

        $this->loginAs($nonApprover);

        $this->postJson("/api/projects/{$project['id']}/stages/{$stageId}/reject-clearance", [
            'notes' => 'Revisi',
        ])->assertStatus(403);
    }

    public function test_can_request_clearance_without_api_prefix(): void
    {
        [$project, $stages] = $this->seedActiveProject();
        $stageId = $stages[0]['id'];

        $this->putJson("/projects/{$project['id']}/stages/{$stageId}", [
            'requires_approval' => true,
        ])->assertStatus(200);

        $response = $this->postJson("/projects/{$project['id']}/stages/{$stageId}/request-clearance", [
            'notes' => 'Route without api prefix works',
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('data.stage.status', 'pending_clearance');
    }
}
