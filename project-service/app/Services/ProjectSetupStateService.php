<?php

namespace App\Services;

use App\Models\Project;

/**
 * Derived wizard completeness for free navigation between steps 1-5.
 *
 * No wizard-step column is stored; every rule below is derived from the
 * project and its stages (see specs/003-project-management-e2e/data-model.md).
 */
class ProjectSetupStateService
{
    public function state(int $projectId): array
    {
        $project = Project::with('stages')->findOrFail($projectId);

        $missing = [];

        $step1 = $this->checkStep1($project, $missing);
        $step2 = $this->checkStep2($project, $missing);
        $step3 = $this->checkStep3($project, $missing);
        $step4 = $this->checkStep4($project, $missing);

        return [
            'steps' => [1 => $step1, 2 => $step2, 3 => $step3, 4 => $step4],
            'ready_to_activate' => $step1 && $step2 && $step3 && $step4,
            'missing' => $missing,
        ];
    }

    private function checkStep1(Project $project, array &$missing): bool
    {
        $complete = true;

        foreach (['title', 'client_id', 'category_id', 'pic_user_id', 'target_end_date'] as $field) {
            if (empty($project->getAttribute($field))) {
                $missing[] = "step_1.{$field}";
                $complete = false;
            }
        }

        return $complete;
    }

    private function checkStep2(Project $project, array &$missing): bool
    {
        if ($project->stages->isEmpty()) {
            $missing[] = 'step_2.stages';

            return false;
        }

        return true;
    }

    private function checkStep3(Project $project, array &$missing): bool
    {
        $complete = true;

        if (empty($project->start_date) || empty($project->target_end_date)) {
            $missing[] = 'step_3.project_dates';
            $complete = false;
        }

        foreach ($project->stages as $stage) {
            if (empty($stage->start_date) || empty($stage->end_date)) {
                $missing[] = "step_3.stage_{$stage->id}.dates";
                $complete = false;
            }
        }

        if ($project->stages->isEmpty()) {
            $complete = false;
        }

        return $complete;
    }

    private function checkStep4(Project $project, array &$missing): bool
    {
        if ($project->stages->isEmpty()) {
            $missing[] = 'step_4.stages';

            return false;
        }

        $complete = true;

        foreach ($project->stages as $stage) {
            if (! $stage->is_configured) {
                $missing[] = "step_4.stage_{$stage->id}.not_configured";
                $complete = false;
            }
        }

        return $complete;
    }
}
