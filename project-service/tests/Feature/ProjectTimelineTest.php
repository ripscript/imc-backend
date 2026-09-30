<?php

namespace Tests\Feature;

use App\Models\ProjectStage;
use Illuminate\Support\Facades\DB;
use Tests\TestProjectCase;

class ProjectTimelineTest extends TestProjectCase
{
    private function seedStages(int $projectId): array
    {

        $a = ProjectStage::create(['project_id' => $projectId, 'name' => 'Requirement', 'sequence' => 1, 'created_by' => 'test']);
        $b = ProjectStage::create(['project_id' => $projectId, 'name' => 'Feasibility', 'sequence' => 2, 'created_by' => 'test']);

        return [$a->id, $b->id];
    }

    public function test_save_timeline_returns_inclusive_durations(): void
    {
        $project = $this->createProject(['start_date' => '2026-06-01']);
        [$aId, $bId] = $this->seedStages($project['id']);

        $response = $this->putJson("/api/projects/{$project['id']}/timeline", [
            'start_date' => '2026-06-01',
            'target_end_date' => '2026-09-30',
            'stages' => [
                ['id' => $aId, 'start_date' => '2026-06-03', 'end_date' => '2026-06-09'],
                ['id' => $bId, 'start_date' => '2026-06-10', 'end_date' => '2026-06-16', 'depends_on_project_stage_id' => $aId],
            ],
        ]);

        $response->assertStatus(200)->assertJsonPath('success', true);

        $data = $response->json('data');
        $this->assertSame(122, $data['total_duration_days']);
        $this->assertSame(7, $data['stages'][0]['duration_days']);
        $this->assertSame(7, $data['stages'][1]['duration_days']);
        $this->assertSame($aId, $data['stages'][1]['depends_on_project_stage_id']);

        // Setup-state step 3 flips to complete.
        $this->getJson("/api/projects/{$project['id']}/setup-state")
            ->assertJsonPath('data.steps.3', true);
    }

    public function test_save_rejects_invalid_ranges(): void
    {
        $project = $this->createProject();
        [$aId] = $this->seedStages($project['id']);

        // Project end before start.
        $this->putJson("/api/projects/{$project['id']}/timeline", [
            'start_date' => '2026-09-30',
            'target_end_date' => '2026-06-01',
        ])->assertStatus(422);

        // Stage end before stage start.
        $this->putJson("/api/projects/{$project['id']}/timeline", [
            'start_date' => '2026-06-01',
            'target_end_date' => '2026-09-30',
            'stages' => [['id' => $aId, 'start_date' => '2026-06-09', 'end_date' => '2026-06-03']],
        ])->assertStatus(422);

        // Stage outside the project period.
        $this->putJson("/api/projects/{$project['id']}/timeline", [
            'start_date' => '2026-06-01',
            'target_end_date' => '2026-09-30',
            'stages' => [['id' => $aId, 'start_date' => '2026-10-01', 'end_date' => '2026-10-07']],
        ])->assertStatus(422);
    }

    public function test_save_rejects_bad_dependencies(): void
    {
        $project = $this->createProject();
        [$aId, $bId] = $this->seedStages($project['id']);

        $payload = fn (array $stages) => [
            'start_date' => '2026-06-01',
            'target_end_date' => '2026-09-30',
            'stages' => $stages,
        ];

        // Self reference.
        $this->putJson("/api/projects/{$project['id']}/timeline", $payload([
            ['id' => $aId, 'depends_on_project_stage_id' => $aId],
        ]))->assertStatus(422);

        // Two-node cycle.
        $this->putJson("/api/projects/{$project['id']}/timeline", $payload([
            ['id' => $aId, 'depends_on_project_stage_id' => $bId],
            ['id' => $bId, 'depends_on_project_stage_id' => $aId],
        ]))->assertStatus(422);

        // Dependent starts before its dependency.
        $this->putJson("/api/projects/{$project['id']}/timeline", $payload([
            ['id' => $aId, 'start_date' => '2026-06-10', 'end_date' => '2026-06-16'],
            ['id' => $bId, 'start_date' => '2026-06-03', 'end_date' => '2026-06-09', 'depends_on_project_stage_id' => $aId],
        ]))->assertStatus(422);

        // Unknown stage id.
        $this->putJson("/api/projects/{$project['id']}/timeline", $payload([
            ['id' => 99999, 'start_date' => '2026-06-03', 'end_date' => '2026-06-09'],
        ]))->assertStatus(422);

        // Clearing a dependency (explicit null) succeeds.
        DB::table('pm_project_stages')->where('id', $bId)->update(['depends_on_project_stage_id' => $aId]);
        $this->putJson("/api/projects/{$project['id']}/timeline", $payload([
            ['id' => $bId, 'depends_on_project_stage_id' => null],
        ]))->assertStatus(200);

        $this->assertNull(DB::table('pm_project_stages')->where('id', $bId)->value('depends_on_project_stage_id'));
    }
}
