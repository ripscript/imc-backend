<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WorkflowBlueprintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTables();
        $this->actingAsAdmin();
    }

    private function seedTables(): void
    {
        DB::statement('CREATE TABLE IF NOT EXISTS ms_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, code TEXT, created_at DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT UNIQUE, password TEXT, role_id INTEGER, created_at DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE IF NOT EXISTS pm_workflows (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT, name TEXT, version INTEGER DEFAULT 1, status TEXT DEFAULT \'active\', created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE IF NOT EXISTS pm_workflow_stages (id INTEGER PRIMARY KEY AUTOINCREMENT, workflow_id INTEGER, code TEXT, name TEXT, description TEXT, sequence INTEGER, is_required INTEGER DEFAULT 1, allow_skip INTEGER DEFAULT 0, allow_repeat INTEGER DEFAULT 0, requires_approval INTEGER DEFAULT 0, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE IF NOT EXISTS pm_workflow_stage_tasks (id INTEGER PRIMARY KEY AUTOINCREMENT, workflow_stage_id INTEGER, title TEXT, description TEXT, priority TEXT DEFAULT \'medium\', sequence INTEGER DEFAULT 1, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE IF NOT EXISTS pm_workflow_stage_deliverables (id INTEGER PRIMARY KEY AUTOINCREMENT, workflow_stage_id INTEGER, document_type_id INTEGER, name TEXT, is_required INTEGER DEFAULT 1, sequence INTEGER DEFAULT 1, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE IF NOT EXISTS pm_document_types (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT, name TEXT, allowed_extension TEXT, max_size_mb INTEGER DEFAULT 10, status TEXT DEFAULT \'active\', created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE IF NOT EXISTS activity_log (id INTEGER PRIMARY KEY AUTOINCREMENT, log_name TEXT, description TEXT, subject_type TEXT, subject_id INTEGER, causer_type TEXT, causer_id INTEGER, properties TEXT, event TEXT, batch_uuid TEXT, created_at DATETIME, updated_at DATETIME)');
    }

    private function actingAsAdmin(): void
    {
        $roleId = DB::table('ms_roles')->insertGetId(['name' => 'Super Admin', 'code' => 'super_admin', 'created_at' => now(), 'updated_at' => now()]);
        $user = User::factory()->create(['role_id' => $roleId]);
        $token = auth('admin')->login($user);
        $this->withHeader('Authorization', 'Bearer '.$token);
    }

    public function test_create_workflow_with_blueprint_children(): void
    {
        $typeId = DB::table('pm_document_types')->insertGetId([
            'code' => 'DT-0001', 'name' => 'Production Plan', 'allowed_extension' => 'pdf,doc,docx',
            'max_size_mb' => 10, 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/workflows', [
            'name' => 'ETO Manufacturing',
            'stages' => [
                [
                    'name' => 'Requirement',
                    'sequence' => 1,
                    'description' => 'Kumpulkan kebutuhan.',
                ],
                [
                    'name' => 'Manufacturing',
                    'sequence' => 2,
                    'requires_approval' => true,
                    'tasks' => [
                        ['title' => 'Persiapan produksi', 'priority' => 'high'],
                        ['title' => 'Setup mesin'],
                    ],
                    'deliverables' => [
                        ['name' => 'Production Plan', 'document_type_id' => $typeId, 'is_required' => true],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);
        $workflowId = $response->json('data.id');

        $this->assertDatabaseHas('pm_workflow_stages', [
            'workflow_id' => $workflowId,
            'name' => 'Manufacturing',
            'description' => null,
            'requires_approval' => 1,
        ]);

        $stageId = DB::table('pm_workflow_stages')->where('workflow_id', $workflowId)->where('name', 'Manufacturing')->value('id');

        $this->assertDatabaseHas('pm_workflow_stage_tasks', [
            'workflow_stage_id' => $stageId,
            'title' => 'Persiapan produksi',
            'priority' => 'high',
        ]);
        $this->assertDatabaseHas('pm_workflow_stage_deliverables', [
            'workflow_stage_id' => $stageId,
            'name' => 'Production Plan',
            'document_type_id' => $typeId,
        ]);

        // Blueprint preview exposes children.
        $preview = $this->getJson("/api/workflows/{$workflowId}/stages")->assertStatus(200)->json('data');
        $manufacturing = collect($preview)->firstWhere('name', 'Manufacturing');
        $this->assertTrue((bool) $manufacturing['requires_approval']);
        $this->assertCount(2, $manufacturing['tasks']);
        $this->assertCount(1, $manufacturing['deliverables']);
        $this->assertSame('Persiapan produksi', $manufacturing['tasks'][0]['title']);
    }

    public function test_update_replaces_blueprint_children(): void
    {
        $workflowId = DB::table('pm_workflows')->insertGetId([
            'code' => 'WF-0001', 'name' => 'Old', 'version' => 1, 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $oldStageId = DB::table('pm_workflow_stages')->insertGetId([
            'workflow_id' => $workflowId, 'code' => 'WS-OLD', 'name' => 'Old Stage', 'sequence' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('pm_workflow_stage_tasks')->insert([
            'workflow_stage_id' => $oldStageId, 'title' => 'Old task', 'sequence' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->putJson("/api/workflows/{$workflowId}", [
            'stages' => [
                ['name' => 'New Stage', 'sequence' => 1],
            ],
        ])->assertStatus(200);

        // Old stage + children are gone (soft-deleted), replaced by the new blueprint.
        $this->assertNotNull(DB::table('pm_workflow_stages')->where('id', $oldStageId)->value('deleted_at'));
        $this->assertNotNull(DB::table('pm_workflow_stage_tasks')->where('workflow_stage_id', $oldStageId)->value('deleted_at'));
        $this->assertSame(1, DB::table('pm_workflow_stages')->where('workflow_id', $workflowId)->whereNull('deleted_at')->count());
        $this->assertSame(0, DB::table('pm_workflow_stage_tasks')->whereNull('deleted_at')->count());
    }

    public function test_create_rejects_bad_nested_payload(): void
    {
        // Bad priority.
        $this->postJson('/api/workflows', [
            'name' => 'Bad Priority',
            'stages' => [
                ['name' => 'S1', 'sequence' => 1, 'tasks' => [['title' => 'T1', 'priority' => 'urgent']]],
            ],
        ])->assertStatus(422);

        // Unknown document type.
        $this->postJson('/api/workflows', [
            'name' => 'Bad Type',
            'stages' => [
                ['name' => 'S1', 'sequence' => 1, 'deliverables' => [['name' => 'D1', 'document_type_id' => 99999]]],
            ],
        ])->assertStatus(422);
    }
}
