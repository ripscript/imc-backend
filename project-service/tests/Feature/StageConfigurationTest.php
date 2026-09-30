<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestProjectCase;

class StageConfigurationTest extends TestProjectCase
{
    private function seedStage(int $projectId): int
    {
        $stages = $this->putJson("/api/projects/{$projectId}/stages", [
            'stages' => [['name' => 'Requirement', 'sequence' => 1]],
        ])->assertStatus(200)->json('data');

        $this->assertSame($projectId, DB::table('pm_project_stages')->where('id', $stages[0]['id'])->value('project_id'));

        return $stages[0]['id'];
    }

    private function seedStages(int $projectId, array $names): array
    {
        $entries = [];

        foreach ($names as $i => $name) {
            $entries[] = ['name' => $name, 'sequence' => $i + 1];
        }

        return $this->putJson("/api/projects/{$projectId}/stages", ['stages' => $entries])
            ->assertStatus(200)
            ->json('data');
    }

    public function test_stage_detail_and_update_marks_configured(): void
    {
        $project = $this->createProject();
        $stageId = $this->seedStage($project['id']);

        $this->getJson("/api/projects/{$project['id']}/stages/{$stageId}")
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Requirement')
            ->assertJsonPath('data.tasks', [])
            ->assertJsonPath('data.deliverables', [])
            ->assertJsonPath('data.documents', [])
            ->assertJsonPath('data.approvers', []);

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", [
            'description' => 'Tahap requirement',
            'allow_skip' => true,
            'requires_approval' => true,
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.is_configured', true)
            ->assertJsonPath('data.description', 'Tahap requirement')
            ->assertJsonPath('data.allow_skip', true);

        // Flagged for approval but no approvers yet → step 4 incomplete (Phase 8 gate).
        $state = $this->getJson("/api/projects/{$project['id']}/setup-state")
            ->assertJsonPath('data.steps.4', false);
        $this->assertContains("step_4.stage_{$stageId}.approvers_missing", $state->json('data.missing'));

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}/approvers", [
            'approvers' => [['approver_id' => $this->admin->id]],
        ])->assertStatus(200);

        $this->getJson("/api/projects/{$project['id']}/setup-state")
            ->assertJsonPath('data.steps.4', true);
    }

    public function test_task_crud_and_toggle_counters(): void
    {
        $project = $this->createProject();
        $stageId = $this->seedStage($project['id']);
        $base = "/api/projects/{$project['id']}/stages/{$stageId}/tasks";

        $task = $this->postJson($base, ['title' => 'Persiapan produksi'])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Persiapan produksi')
            ->json('data');

        $this->postJson($base, ['title' => 'Setup mesin', 'priority' => 'high', 'assignee_id' => $this->admin->id])
            ->assertStatus(201);

        $this->getJson($base)
            ->assertStatus(200)
            ->assertJsonPath('data.total', 2);

        $toggle = $this->patchJson("/api/projects/{$project['id']}/tasks/{$task['id']}/toggle")
            ->assertStatus(200)
            ->json('data');

        $this->assertSame('done', $toggle['status']);
        $this->assertSame(1, $toggle['done_count']);
        $this->assertSame(2, $toggle['total_count']);
        $this->assertSame(50, $toggle['stage_progress']);

        $this->putJson("{$base}/{$task['id']}", ['title' => 'Persiapan produksi (revisi)'])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Persiapan produksi (revisi)');

        $this->deleteJson("{$base}/{$task['id']}")->assertStatus(200);
        $this->getJson($base)->assertJsonPath('data.total', 1);
    }

    public function test_deliverable_crud(): void
    {
        $project = $this->createProject();
        $stageId = $this->seedStage($project['id']);
        $base = "/api/projects/{$project['id']}/stages/{$stageId}/deliverables";

        $deliverable = $this->postJson($base, ['name' => 'Production Plan'])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.is_required', true)
            ->json('data');

        $this->putJson("{$base}/{$deliverable['id']}", ['status' => 'wip'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'wip');

        $this->getJson($base)->assertStatus(200)->assertJsonCount(1, 'data');

        $this->deleteJson("{$base}/{$deliverable['id']}")->assertStatus(200);
    }

    public function test_document_upload_versions_and_validates_type(): void
    {
        $project = $this->createProject();
        $stageId = $this->seedStage($project['id']);
        $base = "/api/projects/{$project['id']}/stages/{$stageId}/documents";

        $typeId = DB::table('pm_document_types')->insertGetId([
            'code' => 'DT-0001', 'name' => 'Production Plan',
            'allowed_extension' => 'pdf,doc,docx', 'max_size_mb' => 10,
            'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $first = $this->postJson($base, [
            'document_type_id' => $typeId,
            'file_name' => 'Production Plan.pdf',
            'file_path' => 'https://files.example.com/plan-v1.pdf',
            'file_size_kb' => 1228,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.version', 1)
            ->assertJsonPath('data.status', 'active')
            ->json('data');

        // Same file_name → new version, previous superseded.
        $second = $this->postJson($base, [
            'document_type_id' => $typeId,
            'file_name' => 'Production Plan.pdf',
            'file_path' => 'https://files.example.com/plan-v2.pdf',
            'file_size_kb' => 1300,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.version', 2)
            ->json('data');

        $this->assertSame('superseded', DB::table('pm_documents')->where('id', $first['id'])->value('status'));

        $this->getJson($base)->assertStatus(200)->assertJsonCount(1, 'data');

        // Disallowed extension for this document type.
        $this->postJson($base, [
            'document_type_id' => $typeId,
            'file_name' => 'Setup.exe',
            'file_path' => 'https://files.example.com/setup.exe',
        ])->assertStatus(422);

        $this->deleteJson("{$base}/{$second['id']}")->assertStatus(200);
    }

    public function test_approver_sync_requires_flag_and_replaces_list(): void
    {
        $project = $this->createProject();
        $stageId = $this->seedStage($project['id']);
        $base = "/api/projects/{$project['id']}/stages/{$stageId}/approvers";

        // Flag off → 422.
        $this->putJson($base, ['approvers' => [['approver_id' => $this->admin->id]]])
            ->assertStatus(422);

        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", ['requires_approval' => true])
            ->assertStatus(200);

        $other = \App\Models\User::factory()->create();

        $synced = $this->putJson($base, ['approvers' => [
            ['approver_id' => $this->admin->id],
            ['approver_id' => $other->id],
        ]])
            ->assertStatus(200)
            ->json('data');

        $this->assertCount(2, $synced);
        $this->assertSame([1, 2], array_column($synced, 'sequence'));
        $this->assertSame('pending', $synced[0]['status']);
        $this->assertSame($this->admin->email, $synced[0]['approver']['email']);

        // Replace with a single approver → other row removed.
        $this->putJson($base, ['approvers' => [['approver_id' => $other->id]]])
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // Switching the flag off clears approval rows.
        $this->putJson("/api/projects/{$project['id']}/stages/{$stageId}", ['requires_approval' => false])
            ->assertStatus(200)
            ->assertJsonPath('data.approvers', []);
    }

    public function test_bulk_configuration_saves_many_stages_in_one_request(): void
    {
        $project = $this->createProject();
        $stages = $this->seedStages($project['id'], ['Requirement', 'Feasibility', 'Production']);
        $other = \App\Models\User::factory()->create();

        $response = $this->putJson("/api/projects/{$project['id']}/stages/configuration", [
            'stages' => [
                [
                    'id' => $stages[0]['id'],
                    'description' => 'Tahap requirement',
                    'allow_skip' => true,
                    'tasks' => [['title' => 'Kumpulkan kebutuhan']],
                    'deliverables' => [['name' => 'Dokumen kebutuhan']],
                ],
                [
                    'id' => $stages[1]['id'],
                    'description' => 'Tahap feasibility',
                    'requires_approval' => true,
                    'approvers' => [['approver_id' => $this->admin->id], ['approver_id' => $other->id]],
                ],
                ['id' => $stages[2]['id'], 'description' => 'Tahap produksi'],
            ],
        ])->assertStatus(200);

        // Response carries the fresh list plus setup-state in one round trip.
        $this->assertCount(3, $response->json('data.stages'));
        $this->assertArrayHasKey('steps', $response->json('data.setup_state'));

        $rows = DB::table('pm_project_stages')->where('project_id', $project['id'])->get()->keyBy('id');

        foreach ($stages as $stage) {
            $this->assertSame(1, (int) $rows[$stage['id']]->is_configured);
        }

        $this->assertSame('Tahap requirement', $rows[$stages[0]['id']]->description);
        $this->assertSame(1, (int) $rows[$stages[0]['id']]->allow_skip);
        $this->assertSame(1, DB::table('pm_tasks')->where('project_stage_id', $stages[0]['id'])->count());
        $this->assertSame(1, DB::table('pm_deliverables')->where('project_stage_id', $stages[0]['id'])->count());
        $this->assertSame(2, DB::table('pm_approvals')->where('project_stage_id', $stages[1]['id'])->count());

        // Requires-approval stage now has approvers → step 4 complete.
        $this->getJson("/api/projects/{$project['id']}/setup-state")
            ->assertJsonPath('data.steps.4', true);
    }

    public function test_bulk_configuration_is_partial_but_marks_all_configured(): void
    {
        $project = $this->createProject();
        $stages = $this->seedStages($project['id'], ['Requirement', 'Feasibility', 'Production']);

        // Send only one of three stages.
        $this->putJson("/api/projects/{$project['id']}/stages/configuration", [
            'stages' => [['id' => $stages[0]['id'], 'description' => 'Hanya stage ini diubah']],
        ])->assertStatus(200);

        $rows = DB::table('pm_project_stages')->where('project_id', $project['id'])->get()->keyBy('id');

        // Listed stage got its content updated.
        $this->assertSame('Hanya stage ini diubah', $rows[$stages[0]['id']]->description);

        // Unlisted stages keep their content untouched...
        $this->assertNull($rows[$stages[1]['id']]->description);
        $this->assertNull($rows[$stages[2]['id']]->description);

        // ...but the bulk save declares step 4 configured for the whole project.
        foreach ($stages as $stage) {
            $this->assertSame(1, (int) $rows[$stage['id']]->is_configured);
        }
    }

    public function test_bulk_configuration_is_atomic_on_failure(): void
    {
        $project = $this->createProject();
        $stages = $this->seedStages($project['id'], ['Requirement', 'Feasibility']);

        $response = $this->putJson("/api/projects/{$project['id']}/stages/configuration", [
            'stages' => [
                ['id' => $stages[0]['id'], 'description' => 'Akan di-rollback'],
                ['id' => $stages[1]['id'], 'tasks' => [['id' => 999999, 'title' => 'Task asing']]],
            ],
        ])->assertStatus(422);

        // Domain error is indexed so FE can jump to the offending stage.
        $errors = $response->json('errors');
        $this->assertArrayHasKey('stages.1.tasks.0.id', $errors);

        // Nothing persisted: neither content nor the configured sweep.
        $rows = DB::table('pm_project_stages')->where('project_id', $project['id'])->get()->keyBy('id');
        $this->assertNull($rows[$stages[0]['id']]->description);

        foreach ($stages as $stage) {
            $this->assertSame(0, (int) $rows[$stage['id']]->is_configured);
        }

        $this->assertSame(0, DB::table('pm_tasks')->where('project_stage_id', $stages[0]['id'])->count());
    }

    public function test_bulk_configuration_rejects_unknown_stage(): void
    {
        $project = $this->createProject();
        $this->seedStage($project['id']);

        $this->putJson("/api/projects/{$project['id']}/stages/configuration", [
            'stages' => [['id' => 999999, 'description' => 'Stage asing']],
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('stages.0.id');
    }

    public function test_bulk_configuration_rejects_empty_payload(): void
    {
        $project = $this->createProject();
        $this->seedStage($project['id']);

        $this->putJson("/api/projects/{$project['id']}/stages/configuration", ['stages' => []])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
