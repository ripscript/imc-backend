<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestProjectCase;

class ProjectCrossCuttingTest extends TestProjectCase
{
    public function test_activities_logged_and_listed_per_project(): void
    {
        $project = $this->createProject();
        $projectId = $project['id'];

        $this->putJson("/api/projects/{$project['id']}", ['title' => 'Renamed'])->assertStatus(200);

        $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [['name' => 'Requirement', 'sequence' => 1]],
        ])->assertStatus(200);

        $response = $this->getJson("/api/projects/{$project['id']}/activities");

        $response->assertStatus(200)->assertJsonPath('success', true);

        $items = $response->json('data.items');
        $this->assertGreaterThanOrEqual(3, $response->json('data.total'));

        // Newest first: the stage creation entry carries its stage id.
        $stageEntry = collect($items)->first(fn ($item) => $item['project_stage_id'] !== null);
        $this->assertNotNull($stageEntry);
        $this->assertSame('create', $stageEntry['activity_type']);
        $this->assertSame($this->admin->name, $stageEntry['user']['name']);

        foreach ($items as $item) {
            $this->assertArrayHasKey('activity_type', $item);
            $this->assertArrayHasKey('description', $item);
        }

        // Every stored row scopes itself to the project via properties.
        $loggedProjectIds = DB::table('activity_log')
            ->where('log_name', 'project-management')
            ->pluck('properties')
            ->map(fn ($props) => json_decode($props, true)['project_id'] ?? null)
            ->all();
        $this->assertContains($projectId, $loggedProjectIds);
    }

    public function test_members_crud_and_pic_delete_guard(): void
    {
        [$project] = $this->seedCompleteProject();

        $this->getJson("/api/projects/{$project['id']}/members")
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');

        $other = User::factory()->create();

        $member = $this->postJson("/api/projects/{$project['id']}/members", [
            'user_id' => $other->id,
            'role' => 'member',
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.user.email', $other->email)
            ->assertJsonPath('data.role', 'member')
            ->json('data');

        // Duplicate membership rejected.
        $this->postJson("/api/projects/{$project['id']}/members", ['user_id' => $other->id])
            ->assertStatus(422);

        $this->deleteJson("/api/projects/{$project['id']}/members/{$member['id']}")
            ->assertStatus(200);

        // Activate → PIC auto-synced; deleting the PIC row is refused.
        $this->postJson("/api/projects/{$project['id']}/activate")->assertStatus(200);

        $members = $this->getJson("/api/projects/{$project['id']}/members")->json('data');
        $picMember = collect($members)->firstWhere('user.email', $this->admin->email);
        $this->assertSame('pic', $picMember['role']);

        $this->deleteJson("/api/projects/{$project['id']}/members/{$picMember['id']}")
            ->assertStatus(422);
    }

    public function test_custom_fields_merged_in_detail_with_casting(): void
    {
        $project = $this->createProject();
        $projectId = $project['id'];

        $fields = [
            ['cf_project_nilai', 'Nilai Kontrak', 'money', '100000000'],
            ['cf_project_prioritas', 'Prioritas', 'boolean', '1'],
            ['cf_project_tags', 'Tags', 'multiselect', '["urgent","eto"]'],
            ['cf_project_catatan', 'Catatan', 'text', 'bebas'],
        ];

        foreach ($fields as [$code, $name, $type, $value]) {
            $fieldId = DB::table('pm_custom_fields')->insertGetId([
                'code' => $code, 'name' => $name, 'entity_type' => 'project',
                'field_type' => $type, 'is_required' => 0, 'status' => 'active',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('pm_custom_field_values')->insert([
                'custom_field_id' => $fieldId, 'entity_type' => 'project',
                'entity_id' => $projectId, 'value' => $value,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $custom = $this->getJson("/api/projects/{$project['id']}")->json('data.custom_fields');

        $this->assertSame(100000000, $custom['cf_project_nilai']);
        $this->assertTrue($custom['cf_project_prioritas']);
        $this->assertSame(['urgent', 'eto'], $custom['cf_project_tags']);
        $this->assertSame('bebas', $custom['cf_project_catatan']);
    }

    public function test_destroy_cascades_soft_deletes_through_aggregate(): void
    {
        $project = $this->createProject();
        $projectId = $project['id'];

        $stages = $this->putJson("/api/projects/{$project['id']}/stages", [
            'stages' => [['name' => 'Requirement', 'sequence' => 1]],
        ])->json('data');

        $this->postJson("/api/projects/{$project['id']}/stages/{$stages[0]['id']}/tasks", [
            'title' => 'Task to cascade',
        ])->assertStatus(201);

        $this->deleteJson("/api/projects/{$project['id']}")->assertStatus(200);

        $this->assertNotNull(DB::table('content_detail_projects')->where('id', $projectId)->value('deleted_at'));
        $this->assertNotNull(DB::table('pm_project_stages')->where('project_id', $projectId)->value('deleted_at'));
        $this->assertNotNull(DB::table('pm_tasks')->where('project_stage_id', $stages[0]['id'])->value('deleted_at'));
    }
}
