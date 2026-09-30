<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectStage;

trait ResolvesProjectStage
{
    protected function resolveStage(int $projectId, int $stageId): ProjectStage
    {
        $project = Project::findOrFail($projectId);

        return ProjectStage::where('project_id', $project->id)
            ->where('id', $stageId)
            ->firstOrFail();
    }
}
