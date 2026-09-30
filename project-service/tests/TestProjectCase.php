<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

/**
 * Base test case for project-management feature tests.
 *
 * Domain tables live in migration-service (shared Postgres), so the SQLite
 * test database is bootstrapped with raw DDL mirroring those tables.
 */
abstract class TestProjectCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected int $contentId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestSchema();
        $this->actingAsAdmin();
        $this->contentId = DB::table('contents')->insertGetId(['type' => 'project']);
        DB::table('ms_layout')->insert(['name' => 'Project', 'type' => 'project']);
    }

    protected function actingAsAdmin(): void
    {
        $roleId = DB::table('ms_roles')->insertGetId([
            'name' => 'Super Admin',
            'code' => 'super_admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->admin = User::factory()->create(['role_id' => $roleId]);
        $token = auth('admin')->login($this->admin);
        $this->withHeader('Authorization', 'Bearer '.$token);
    }

    protected function createProject(array $overrides = []): array
    {
        $clientId = DB::table('pm_clients')->insertGetId([
            'name' => 'PT Indofest Mentari',
            'identifier' => 'CL-'.random_int(1000, 9999).'-'.random_int(100, 999),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $categoryId = DB::table('pm_project_categories')->insertGetId([
            'code' => 'PC-'.random_int(1000, 9999).'-'.random_int(100, 999),
            'name' => 'Manufacturing',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/projects', array_merge([
            'content_id' => $this->contentId,
            'title' => 'Development Mesin Giling',
            'client_id' => $clientId,
            'category_id' => $categoryId,
            'pic_user_id' => $this->admin->id,
            'target_end_date' => '2026-09-30',
        ], $overrides));

        $response->assertStatus(201);

        return $response->json('data');
    }

    /**
     * Build a project that passes all setup-state gates (steps 1-4):
     * two dated stages with a dependency, both configured.
     *
     * @return array{0: array, 1: array} [project, stages]
     */
    protected function seedCompleteProject(array $overrides = []): array
    {
        $project = $this->createProject($overrides);

        $stages = $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [
                ['name' => 'Requirement', 'sequence' => 1],
                ['name' => 'Feasibility', 'sequence' => 2],
            ],
        ])->assertStatus(200)->json('data');

        $this->putJson("/api/projects/{$project['id']}/timeline", [
            'start_date' => '2026-06-01',
            'target_end_date' => '2026-09-30',
            'stages' => [
                ['id' => $stages[0]['id'], 'start_date' => '2026-06-03', 'end_date' => '2026-06-09'],
                ['id' => $stages[1]['id'], 'start_date' => '2026-06-10', 'end_date' => '2026-06-16', 'depends_on_project_stage_id' => $stages[0]['id']],
            ],
        ])->assertStatus(200);

        foreach ($stages as $stage) {
            $this->putJson("/api/projects/{$project['id']}/stages/{$stage['id']}", ['description' => 'Configured'])
                ->assertStatus(200);
        }

        return [$project, $stages];
    }

    private function createTestSchema(): void
    {
        // Stock users migration lacks role_id; rebuild the table for tests.
        DB::statement('DROP TABLE IF EXISTS users');
        DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT UNIQUE, email_verified_at DATETIME, password TEXT, role_id INTEGER, remember_token TEXT, created_at DATETIME, updated_at DATETIME)');

        $tables = [
            'CREATE TABLE IF NOT EXISTS ms_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, code TEXT, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS contents (id INTEGER PRIMARY KEY AUTOINCREMENT, type TEXT)',
            'CREATE TABLE IF NOT EXISTS ms_layout (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, type TEXT)',
            'CREATE TABLE IF NOT EXISTS content_detail_projects (id INTEGER PRIMARY KEY AUTOINCREMENT, content_id INTEGER, title TEXT, slug TEXT, description TEXT, thumbnail_path TEXT, is_publicated INTEGER DEFAULT 1, publish_date DATE, layout_id INTEGER, kategori_content_id INTEGER, duration TEXT, location TEXT, start_date DATE, budget INTEGER, organization_address TEXT, organization_name TEXT, project_no TEXT, client_id INTEGER, partner_id INTEGER, category_id INTEGER, workflow_id INTEGER, pic_user_id INTEGER, status TEXT DEFAULT \'draft\', progress INTEGER DEFAULT 0, target_end_date DATE, has_additional_image INTEGER DEFAULT 0, has_additional_video INTEGER DEFAULT 0, has_additional_file INTEGER DEFAULT 0, has_additional_link INTEGER DEFAULT 0, has_additional_text INTEGER DEFAULT 0, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS content_tags (id INTEGER PRIMARY KEY AUTOINCREMENT, content_type TEXT, content_id INTEGER, tag_id INTEGER, created_by TEXT, updated_by TEXT, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS content_additional_images (id INTEGER PRIMARY KEY AUTOINCREMENT, content_type TEXT, content_id INTEGER)',
            'CREATE TABLE IF NOT EXISTS content_additional_videos (id INTEGER PRIMARY KEY AUTOINCREMENT, content_type TEXT, content_id INTEGER)',
            'CREATE TABLE IF NOT EXISTS content_additional_files (id INTEGER PRIMARY KEY AUTOINCREMENT, content_type TEXT, content_id INTEGER)',
            'CREATE TABLE IF NOT EXISTS content_additional_links (id INTEGER PRIMARY KEY AUTOINCREMENT, content_type TEXT, content_id INTEGER)',
            'CREATE TABLE IF NOT EXISTS content_additional_texts (id INTEGER PRIMARY KEY AUTOINCREMENT, content_type TEXT, content_id INTEGER)',
            'CREATE TABLE IF NOT EXISTS project_attachments (id INTEGER PRIMARY KEY AUTOINCREMENT, project_id INTEGER, file_url TEXT, file_name TEXT, file_size_kb INTEGER, created_by TEXT, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_clients (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, identifier TEXT, status TEXT DEFAULT \'active\', created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_partners (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, identifier TEXT, status TEXT DEFAULT \'active\', created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_project_categories (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT, name TEXT, status TEXT DEFAULT \'active\', created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_workflows (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT, name TEXT, version INTEGER DEFAULT 1, status TEXT DEFAULT \'active\', created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_workflow_stages (id INTEGER PRIMARY KEY AUTOINCREMENT, workflow_id INTEGER, code TEXT, name TEXT, description TEXT, sequence INTEGER, is_required INTEGER DEFAULT 1, allow_skip INTEGER DEFAULT 0, allow_repeat INTEGER DEFAULT 0, requires_approval INTEGER DEFAULT 0, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_document_types (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT, name TEXT, allowed_extension TEXT, max_size_mb INTEGER DEFAULT 10, status TEXT DEFAULT \'active\', created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_workflow_stage_tasks (id INTEGER PRIMARY KEY AUTOINCREMENT, workflow_stage_id INTEGER, title TEXT, description TEXT, priority TEXT DEFAULT \'medium\', sequence INTEGER DEFAULT 1, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_workflow_stage_deliverables (id INTEGER PRIMARY KEY AUTOINCREMENT, workflow_stage_id INTEGER, document_type_id INTEGER, name TEXT, is_required INTEGER DEFAULT 1, sequence INTEGER DEFAULT 1, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_project_stages (id INTEGER PRIMARY KEY AUTOINCREMENT, project_id INTEGER, workflow_stage_id INTEGER, name TEXT, description TEXT, sequence INTEGER, status TEXT DEFAULT \'pending\', progress INTEGER DEFAULT 0, pic_user_id INTEGER, start_date DATE, end_date DATE, completed_at DATETIME, depends_on_project_stage_id INTEGER, is_required INTEGER DEFAULT 1, allow_skip INTEGER DEFAULT 0, allow_repeat INTEGER DEFAULT 0, requires_approval INTEGER DEFAULT 0, is_configured INTEGER DEFAULT 0, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_tasks (id INTEGER PRIMARY KEY AUTOINCREMENT, project_stage_id INTEGER, title TEXT, description TEXT, status TEXT DEFAULT \'pending\', priority TEXT DEFAULT \'medium\', assignee_id INTEGER, due_date DATE, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_deliverables (id INTEGER PRIMARY KEY AUTOINCREMENT, project_stage_id INTEGER, document_type_id INTEGER, name TEXT, is_required INTEGER DEFAULT 1, status TEXT DEFAULT \'pending\', due_date DATE, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_documents (id INTEGER PRIMARY KEY AUTOINCREMENT, project_id INTEGER, project_stage_id INTEGER, document_type_id INTEGER, file_name TEXT, file_path TEXT, file_size_kb INTEGER, version INTEGER DEFAULT 1, status TEXT DEFAULT \'active\', uploaded_by INTEGER, uploaded_at DATETIME, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_approvals (id INTEGER PRIMARY KEY AUTOINCREMENT, project_stage_id INTEGER, sequence INTEGER DEFAULT 1, approver_id INTEGER, requested_by INTEGER, approved_by INTEGER, status TEXT DEFAULT \'pending\', notes TEXT, requested_at DATETIME, approved_at DATETIME, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_milestones (id INTEGER PRIMARY KEY AUTOINCREMENT, project_id INTEGER, name TEXT, target_date DATE, status TEXT DEFAULT \'pending\', completed_at DATETIME, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_change_requests (id INTEGER PRIMARY KEY AUTOINCREMENT, project_id INTEGER, project_stage_id INTEGER, change_no TEXT, title TEXT, reason TEXT, impact_scope TEXT, status TEXT DEFAULT \'draft\', requested_by INTEGER, approved_by INTEGER, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_project_members (id INTEGER PRIMARY KEY AUTOINCREMENT, project_id INTEGER, user_id INTEGER, role TEXT DEFAULT \'member\', status TEXT DEFAULT \'active\', created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_custom_fields (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT, name TEXT, entity_type TEXT, field_type TEXT, is_required INTEGER DEFAULT 0, status TEXT DEFAULT \'active\', created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS pm_custom_field_values (id INTEGER PRIMARY KEY AUTOINCREMENT, custom_field_id INTEGER, entity_type TEXT, entity_id INTEGER, value TEXT, created_by TEXT, updated_by TEXT, deleted_at DATETIME, created_at DATETIME, updated_at DATETIME)',
            'CREATE TABLE IF NOT EXISTS activity_log (id INTEGER PRIMARY KEY AUTOINCREMENT, log_name TEXT, description TEXT, subject_type TEXT, subject_id INTEGER, causer_type TEXT, causer_id INTEGER, properties TEXT, event TEXT, batch_uuid TEXT, created_at DATETIME, updated_at DATETIME)',
        ];

        foreach ($tables as $sql) {
            DB::statement($sql);
        }
    }
}
