<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestProjectCase;

class WorkflowBlueprintTest extends TestProjectCase
{
    /**
     * Seed a template workflow with blueprint children.
     *
     * @return array{0: int, 1: array} [workflowId, templateStageIds]
     */
    private function seedTemplate(): array
    {
        $workflowId = DB::table('pm_workflows')->insertGetId([
            'code' => 'WF-0001', 'name' => 'ETO Manufacturing', 'version' => 1,
            'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $reqId = DB::table('pm_workflow_stages')->insertGetId([
            'workflow_id' => $workflowId, 'code' => 'WS-1-1', 'name' => 'Requirement',
            'description' => 'Kumpulkan kebutuhan.', 'sequence' => 1,
            'is_required' => 1, 'allow_skip' => 0, 'allow_repeat' => 0, 'requires_approval' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $mfgId = DB::table('pm_workflow_stages')->insertGetId([
            'workflow_id' => $workflowId, 'code' => 'WS-1-2', 'name' => 'Manufacturing',
            'description' => 'Produksi.', 'sequence' => 2,
            'is_required' => 1, 'allow_skip' => 0, 'allow_repeat' => 0, 'requires_approval' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('pm_workflow_stage_tasks')->insert([
            'workflow_stage_id' => $mfgId, 'title' => 'Persiapan produksi',
            'description' => 'Siapkan area.', 'priority' => 'high', 'sequence' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('pm_workflow_stage_deliverables')->insert([
            'workflow_stage_id' => $mfgId, 'document_type_id' => null, 'name' => 'Production Plan',
            'is_required' => 1, 'sequence' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$workflowId, [$reqId, $mfgId]];
    }

    public function test_sync_stores_exactly_what_is_sent(): void
    {
        $project = $this->createProject();
        [$workflowId, $templateIds] = $this->seedTemplate();

        // FE merged the blueprint in its own state and submits the full
        // tree. The stored rows must match the payload — not the template
        // (titles below deliberately differ from the template rows).
        $stages = $this->putJson("/api/projects/{$project['id']}/stages", [
            'workflow_id' => $workflowId,
            'stages' => [
                [
                    'workflow_stage_id' => $templateIds[1],
                    'name' => 'Manufacturing',
                    'sequence' => 1,
                    'description' => 'Custom desc dari FE.',
                    'requires_approval' => true,
                    'tasks' => [
                        ['title' => 'Task versi FE', 'priority' => 'low'],
                    ],
                    'deliverables' => [
                        ['name' => 'Deliverable versi FE', 'is_required' => false],
                    ],
                ],
            ],
        ])->assertStatus(200)->json('data');

        $this->assertSame('Custom desc dari FE.', $stages[0]['description']);
        $this->assertTrue($stages[0]['requires_approval']);

        $detail = $this->getJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}")
            ->assertStatus(200)->json('data');

        $this->assertCount(1, $detail['tasks']);
        $this->assertSame('Task versi FE', $detail['tasks'][0]['title']);
        $this->assertSame('low', $detail['tasks'][0]['priority']);
        $this->assertCount(1, $detail['deliverables']);
        $this->assertSame('Deliverable versi FE', $detail['deliverables'][0]['name']);
        $this->assertFalse($detail['deliverables'][0]['is_required']);
    }

    public function test_no_rows_created_until_explicit_save(): void
    {
        $project = $this->createProject();
        [$workflowId, $templateIds] = $this->seedTemplate();

        // Barebone sync: structure only, no nested keys.
        $stages = $this->putJson("/api/projects/{$project['id']}/stages", [
            'workflow_id' => $workflowId,
            'stages' => [
                ['workflow_stage_id' => $templateIds[1], 'name' => 'Manufacturing', 'sequence' => 1],
            ],
        ])->assertStatus(200)->json('data');

        $this->assertNull($stages[0]['description']);
        $this->assertFalse($stages[0]['requires_approval']);

        // Reading the stage creates nothing — no implicit snapshot.
        $detail = $this->getJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}")
            ->assertStatus(200)->json('data');

        $this->assertSame([], $detail['tasks']);
        $this->assertSame([], $detail['deliverables']);
        $this->assertSame(0, DB::table('pm_tasks')->where('project_stage_id', $stages[0]['id'])->count());
    }

    public function test_nested_replace_all_on_sync(): void
    {
        $project = $this->createProject();
        [$workflowId, $templateIds] = $this->seedTemplate();

        $stages = $this->putJson("/api/projects/{$project['id']}/stages", [
            'workflow_id' => $workflowId,
            'stages' => [
                [
                    'workflow_stage_id' => $templateIds[1],
                    'name' => 'Manufacturing',
                    'sequence' => 1,
                    'tasks' => [
                        ['title' => 'Task A'],
                        ['title' => 'Task B'],
                    ],
                ],
            ],
        ])->assertStatus(200)->json('data');

        $taskIds = collect(
            $this->getJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}/tasks")->json('data.items')
        )->pluck('id', 'title');

        // Re-sync: rename A, drop B, add C → stored set matches exactly.
        $this->putJson("/api/projects/{$project['id']}/stages", [
            'workflow_id' => $workflowId,
            'stages' => [
                [
                    'id' => $stages[0]['id'],
                    'workflow_stage_id' => $templateIds[1],
                    'name' => 'Manufacturing',
                    'sequence' => 1,
                    'tasks' => [
                        ['id' => $taskIds['Task A'], 'title' => 'Task A renamed'],
                        ['title' => 'Task C'],
                    ],
                ],
            ],
        ])->assertStatus(200);

        $titles = collect(
            $this->getJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}/tasks")->json('data.items')
        )->pluck('title')->sort()->values()->all();

        $this->assertSame(['Task A renamed', 'Task C'], $titles);
    }

    public function test_nested_replace_all_on_stage_update(): void
    {
        $project = $this->createProject();
        [$workflowId, $templateIds] = $this->seedTemplate();

        $stages = $this->putJson("/api/projects/{$project['id']}/stages", [
            'workflow_id' => $workflowId,
            'stages' => [
                ['workflow_stage_id' => $templateIds[1], 'name' => 'Manufacturing', 'sequence' => 1],
            ],
        ])->assertStatus(200)->json('data');
        $stageId = $stages[0]['id'];

        // S5 save with the full tree (what the FE form submits).
        $detail = $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", [
            'description' => 'Config dari FE.',
            'requires_approval' => true,
            'tasks' => [
                ['title' => 'Task 1', 'priority' => 'high'],
            ],
            'deliverables' => [
                ['name' => 'Deliverable 1'],
            ],
        ])->assertStatus(200)->json('data');

        $this->assertTrue($detail['is_configured']);
        $this->assertCount(1, $detail['tasks']);
        $this->assertCount(1, $detail['deliverables']);

        // Omitting the keys leaves children untouched; explicit [] clears.
        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", [
            'description' => 'Config revisi.',
        ])->assertStatus(200)->assertJsonCount(1, 'data.tasks');

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", [
            'tasks' => [],
        ])->assertStatus(200)->assertJsonCount(0, 'data.tasks');
    }

    public function test_nested_entries_validate_ownership_and_required_fields(): void
    {
        $project = $this->createProject();

        // New nested task without title → 422.
        $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [
                ['name' => 'S1', 'sequence' => 1, 'tasks' => [['priority' => 'high']]],
            ],
        ])->assertStatus(422);

        // Foreign task id → 422.
        $stages = $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [['name' => 'S1', 'sequence' => 1]],
        ])->assertStatus(200)->json('data');

        $this->putJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}", [
            'tasks' => [['id' => 99999, 'title' => 'Ghost']],
        ])->assertStatus(422);
    }

    public function test_activation_gate_requires_approvers_where_flagged(): void
    {
        [$project, $stages] = $this->seedCompleteProject();

        $this->putJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}", ['requires_approval' => true])
            ->assertStatus(200);

        $response = $this->postJson("/api/projects/{$project['id']}/activate");
        $response->assertStatus(422);
        $this->assertContains("step_4.stage_{$stages[0]['id']}.approvers_missing", $response->json('errors'));

        // After assigning an approver the gate passes.
        $this->putJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}/approvers", [
            'approvers' => [['approver_id' => $this->admin->id]],
        ])->assertStatus(200);
        $this->postJson("/api/projects/{$project['id']}/activate")->assertStatus(200);
    }

    public function test_manual_stages_stay_blank(): void
    {
        $project = $this->createProject();
        $this->seedTemplate();

        $stages = $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [['name' => 'Manual', 'sequence' => 1]],
        ])->assertStatus(200)->json('data');

        $this->getJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}/tasks")
            ->assertJsonPath('data.total', 0);
        $this->getJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}/deliverables")
            ->assertJsonCount(0, 'data');
        $this->assertFalse($stages[0]['requires_approval']);
    }
}
