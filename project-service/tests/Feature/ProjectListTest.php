<?php

namespace Tests\Feature;

use App\Models\PmTask;
use App\Models\ProjectStage;
use Illuminate\Support\Facades\DB;
use Tests\TestProjectCase;

class ProjectListTest extends TestProjectCase
{
    public function test_list_shows_current_stage_and_derived_progress(): void
    {
        $project = $this->createProject(['title' => 'Development Mesin Giling']);
        $projectId = $project['id'];

        $stage1 = ProjectStage::create([
            'project_id' => $projectId, 'name' => 'Requirement', 'sequence' => 1,
            'status' => 'completed', 'created_by' => 'test',
        ]);
        $stage2 = ProjectStage::create([
            'project_id' => $projectId, 'name' => 'Feasibility', 'sequence' => 2,
            'status' => 'pending', 'created_by' => 'test',
        ]);
        PmTask::create(['project_stage_id' => $stage2->id, 'title' => 'Task 1', 'status' => 'done']);
        PmTask::create(['project_stage_id' => $stage2->id, 'title' => 'Task 2', 'status' => 'pending']);

        $response = $this->getJson('/api/projects');

        $response->assertStatus(200)->assertJsonPath('success', true);

        $item = collect($response->json('data.items'))->firstWhere('project_no', $project['project_no']);

        // Stage 1 completed (100), stage 2 half done (50) → project progress 75.
        $this->assertSame(75, $item['progress']);
        $this->assertSame('Feasibility', $item['current_stage']['name']);
        $this->assertSame(2, $item['current_stage']['sequence']);
        $this->assertSame('PT Indofest Mentari', $item['client']['name']);
        $this->assertSame('draft', $item['status']);
    }

    public function test_list_supports_sort_search_and_filters(): void
    {
        $alpha = $this->createProject(['title' => 'Alpha Project']);
        $beta = $this->createProject(['title' => 'Beta Project']);

        $betaId = $beta['id'];
        ProjectStage::create([
            'project_id' => $betaId, 'name' => 'Requirement', 'sequence' => 1,
            'status' => 'completed', 'created_by' => 'test',
        ]);

        // Sort by progress desc → beta (100) first.
        $titles = collect($this->getJson('/api/projects?sort_by=progress&sort_order=desc')->json('data.items'))
            ->pluck('name')->all();
        $this->assertSame('Beta Project', $titles[0]);

        // Sort by title asc → alpha first.
        $titles = collect($this->getJson('/api/projects?sort_by=title&sort_order=asc')->json('data.items'))
            ->pluck('name')->all();
        $this->assertSame('Alpha Project', $titles[0]);

        // Search matches project name.
        $this->getJson('/api/projects?search=Beta')
            ->assertStatus(200)
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.items.0.name', 'Beta Project');

        // Status filter.
        $this->getJson('/api/projects?status=draft')
            ->assertJsonPath('data.total', 2);

        // Client filter.
        $clientId = DB::table('content_detail_projects')->where('id', $alpha['id'])->value('client_id');
        $this->getJson("/api/projects?client_id={$clientId}")
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.items.0.project_no', $alpha['project_no']);

        // Pagination meta.
        $this->getJson('/api/projects?limit=1&page=2')
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.per_page', 1);
    }

    public function test_my_filter_returns_only_own_projects(): void
    {
        $mine = $this->createProject(['title' => 'My Project']);

        $other = \App\Models\User::factory()->create();
        $this->createProject(['title' => 'Other Project', 'pic_user_id' => $other->id]);

        $response = $this->getJson('/api/projects?my=true');

        $response->assertStatus(200)->assertJsonPath('data.total', 1);
        $this->assertSame($mine['project_no'], $response->json('data.items.0.project_no'));
    }
}
