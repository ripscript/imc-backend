<?php

namespace Tests\Feature;

use App\Models\ProjectStage;
use Tests\TestProjectCase;

class StageApproverTest extends TestProjectCase
{
    public function test_can_list_and_sync_stage_approvers_with_old_payload_format(): void
    {
        $project = $this->createProject(['title' => 'EV Motor Project']);
        $stage = ProjectStage::create([
            'project_id' => $project['id'],
            'name' => 'Design Review',
            'sequence' => 1,
            'status' => 'in_progress',
            'requires_approval' => true,
        ]);

        $approver1 = $this->makeUser('admin_pusat');
        $approver2 = $this->makeUser('super_admin');

        // Test GET empty
        $response = $this->getJson("/api/projects/{$project['id']}/stages/{$stage->id}/approvers");
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', []);

        // Test PUT sync with old format: { "approvers": [ { "approver_id": X }, ... ] }
        $syncResponse = $this->putJson("/api/projects/{$project['id']}/stages/{$stage->id}/approvers", [
            'approvers' => [
                ['approver_id' => $approver1->id],
                ['approver_id' => $approver2->id],
            ],
        ]);

        $syncResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.approver_id', $approver1->id)
            ->assertJsonPath('data.1.approver_id', $approver2->id);

        $this->assertDatabaseHas('pm_stage_approvers', [
            'project_stage_id' => $stage->id,
            'user_id' => $approver1->id,
        ]);
        $this->assertDatabaseHas('pm_stage_approvers', [
            'project_stage_id' => $stage->id,
            'user_id' => $approver2->id,
        ]);

        // Test GET after sync
        $getResponse = $this->getJson("/api/projects/{$project['id']}/stages/{$stage->id}/approvers");
        $getResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.approver_id', $approver1->id)
            ->assertJsonPath('data.0.approver.name', $approver1->name);
    }

    public function test_sync_approvers_validates_required_and_distinct_ids(): void
    {
        $project = $this->createProject(['title' => 'EV Motor Project']);
        $stage = ProjectStage::create([
            'project_id' => $project['id'],
            'name' => 'Design Review',
            'sequence' => 1,
            'status' => 'in_progress',
            'requires_approval' => true,
        ]);

        $response = $this->putJson("/api/projects/{$project['id']}/stages/{$stage->id}/approvers", [
            'approvers' => [
                ['approver_id' => 9999], // not exist
            ],
        ]);
        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
