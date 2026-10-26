<?php

namespace Tests\Feature;

use App\Models\ProjectStage;
use Illuminate\Support\Facades\DB;
use Tests\TestProjectCase;

class ProjectStageTest extends TestProjectCase
{
    private function seedWorkflow(): array
    {
        $workflowId = DB::table('pm_workflows')->insertGetId([
            'code' => 'WF-0001', 'name' => 'ETO Manufacturing', 'version' => 1,
            'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $templateIds = [];
        foreach (['Requirement', 'Feasibility', 'Proposal'] as $i => $name) {
            $templateIds[] = DB::table('pm_workflow_stages')->insertGetId([
                'workflow_id' => $workflowId,
                'code' => 'WS-'.$workflowId.'-'.($i + 1),
                'name' => $name,
                'sequence' => $i + 1,
                'is_required' => 1,
                'allow_skip' => $name === 'Feasibility' ? 1 : 0,
                'allow_repeat' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [$workflowId, $templateIds];
    }

    public function test_sync_instantiates_stages_from_workflow(): void
    {
        $project = $this->createProject();
        [$workflowId, $templateIds] = $this->seedWorkflow();

        $response = $this->putJson("/api/projects/{$project['id']}/stages", [
            'workflow_id' => $workflowId,
            'stages' => [
                ['workflow_stage_id' => $templateIds[0], 'name' => 'Requirement', 'sequence' => 1],
                ['workflow_stage_id' => $templateIds[1], 'name' => 'Feasibility', 'sequence' => 2, 'allow_skip' => true],
            ],
        ]);

        $response->assertStatus(200)->assertJsonPath('success', true);

        $stages = $response->json('data');
        $this->assertCount(2, $stages);
        $this->assertSame([1, 2], array_column($stages, 'sequence'));
        $this->assertSame($templateIds[0], $stages[0]['workflow_stage_id']);
        // Flags come from the submitted payload only — the template is
        // never read for content (Phase 10).
        $this->assertTrue($stages[0]['is_required']);
        $this->assertTrue($stages[1]['allow_skip']);

        $this->assertDatabaseHas('content_detail_projects', [
            'project_no' => $project['project_no'],
            'workflow_id' => $workflowId,
        ]);

        // Setup-state step 2 flips to complete; stages list endpoint matches.
        $this->getJson("/api/projects/{$project['id']}/setup-state")
            ->assertJsonPath('data.steps.2', true);
        $this->getJson("/api/projects/{$project['id']}/stages")
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_sync_supports_manual_stages_and_replace_all_edits(): void
    {
        $project = $this->createProject();

        $first = $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [
                ['name' => 'Manual A', 'sequence' => 1],
                ['name' => 'Manual B', 'sequence' => 2],
            ],
        ])->assertStatus(200)->json('data');

        $this->assertNull($first[0]['workflow_stage_id']);

        // Rename A, drop B, add C, reorder via sequence.
        $second = $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [
                ['id' => $first[1]['id'], 'name' => 'Manual B kept', 'sequence' => 5],
                ['name' => 'Manual C', 'sequence' => 1],
            ],
        ])->assertStatus(200)->json('data');

        $this->assertCount(2, $second);
        $this->assertSame([1, 2], array_column($second, 'sequence'));
        $this->assertSame('Manual C', $second[0]['name']);
        $this->assertSame('Manual B kept', $second[1]['name']);
        $this->assertSoftDeleted('pm_project_stages', ['id' => $first[0]['id']]);
    }

    public function test_sync_reorders_existing_stages_without_unique_collision(): void
    {
        $project = $this->createProject();

        $first = $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [
                ['name' => 'Stage 1', 'sequence' => 1],
                ['name' => 'Stage 2', 'sequence' => 2],
            ],
        ])->assertStatus(200)->json('data');

        // Swap their order
        $second = $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [
                ['id' => $first[1]['id'], 'name' => 'Stage 2 now first', 'sequence' => 1],
                ['id' => $first[0]['id'], 'name' => 'Stage 1 now second', 'sequence' => 2],
            ],
        ]);

        $second->assertStatus(200);
    }

    public function test_sync_rejects_duplicate_sequence_and_foreign_ids(): void
    {
        $project = $this->createProject();

        $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [
                ['name' => 'A', 'sequence' => 1],
                ['name' => 'B', 'sequence' => 1],
            ],
        ])->assertStatus(422);

        $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [['id' => 99999, 'name' => 'Ghost', 'sequence' => 1]],
        ])->assertStatus(422);
    }

    public function test_sync_refuses_removing_a_dependency_target(): void
    {
        $project = $this->createProject();
        $projectId = $project['id'];

        $a = ProjectStage::create(['project_id' => $projectId, 'name' => 'A', 'sequence' => 1, 'created_by' => 'test']);
        $b = ProjectStage::create([
            'project_id' => $projectId, 'name' => 'B', 'sequence' => 2,
            'depends_on_project_stage_id' => $a->id, 'created_by' => 'test',
        ]);

        // Removing A while surviving B depends on it must fail.
        $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [['id' => $b->id, 'name' => 'B', 'sequence' => 1]],
        ])->assertStatus(422);
    }
}
