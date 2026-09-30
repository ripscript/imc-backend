<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Support\StageDependencies;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProjectTimelineService
{
    /**
     * Save the project period, per-stage dates, and dependency links (S4).
     *
     * Entries are partial: only the provided fields of the listed stages are
     * updated. Duration is derived inclusively (end - start + 1 day).
     *
     * @return array{start_date: string, target_end_date: string, total_duration_days: int, stages: array}
     */
    public function save(int $projectId, array $data, ?string $actor = null): array
    {
        return DB::transaction(function () use ($projectId, $data, $actor) {
            $project = Project::findOrFail($projectId);

            $projectStart = Carbon::parse($data['start_date'])->startOfDay();
            $projectEnd = Carbon::parse($data['target_end_date'])->startOfDay();

            if ($projectEnd->lt($projectStart)) {
                throw new ValidationException(
                    'Tanggal selesai proyek harus setelah tanggal mulai proyek.',
                    ['target_end_date' => ['Tanggal selesai proyek harus setelah tanggal mulai proyek.']]
                );
            }

            $entries = collect($data['stages'] ?? []);
            $stages = ProjectStage::where('project_id', $project->id)->get()->keyBy('id');

            foreach ($entries as $entry) {
                if (! $stages->has($entry['id'])) {
                    throw new ValidationException(
                        'Stage tidak ditemukan pada proyek ini.',
                        ['stages' => ["Stage id {$entry['id']} bukan bagian dari proyek {$projectId}."]]
                    );
                }
            }

            // Effective dates/links: existing values overridden by the payload.
            $effective = [];
            foreach ($stages as $id => $stage) {
                $effective[$id] = [
                    'start' => $stage->start_date ? Carbon::parse($stage->start_date)->startOfDay() : null,
                    'end' => $stage->end_date ? Carbon::parse($stage->end_date)->startOfDay() : null,
                    'depends_on' => $stage->depends_on_project_stage_id,
                ];
            }
            foreach ($entries as $entry) {
                if (array_key_exists('start_date', $entry)) {
                    $effective[$entry['id']]['start'] = $entry['start_date'] ? Carbon::parse($entry['start_date'])->startOfDay() : null;
                }
                if (array_key_exists('end_date', $entry)) {
                    $effective[$entry['id']]['end'] = $entry['end_date'] ? Carbon::parse($entry['end_date'])->startOfDay() : null;
                }
                if (array_key_exists('depends_on_project_stage_id', $entry)) {
                    $effective[$entry['id']]['depends_on'] = $entry['depends_on_project_stage_id'];
                }
            }

            $this->validateStageDates($stages, $effective, $projectStart, $projectEnd);
            $this->validateDependencies($stages, $effective);

            $project->start_date = $projectStart->toDateString();
            $project->target_end_date = $projectEnd->toDateString();
            $project->updated_by = $actor;
            $project->save();

            foreach ($entries as $entry) {
                $stage = $stages[$entry['id']];
                $updates = ['updated_by' => $actor];

                if (array_key_exists('start_date', $entry)) {
                    $updates['start_date'] = $entry['start_date'];
                }
                if (array_key_exists('end_date', $entry)) {
                    $updates['end_date'] = $entry['end_date'];
                }
                if (array_key_exists('depends_on_project_stage_id', $entry)) {
                    $updates['depends_on_project_stage_id'] = $entry['depends_on_project_stage_id'];
                }

                $stage->fill($updates);
                $stage->save();
            }

            $fresh = ProjectStage::where('project_id', $project->id)->orderBy('sequence')->get();

            return [
                'start_date' => $projectStart->toDateString(),
                'target_end_date' => $projectEnd->toDateString(),
                'total_duration_days' => self::durationDays($projectStart, $projectEnd),
                'stages' => $fresh->map(fn (ProjectStage $stage) => [
                    'id' => $stage->id,
                    'name' => $stage->name,
                    'start_date' => $stage->start_date?->toDateString(),
                    'end_date' => $stage->end_date?->toDateString(),
                    'duration_days' => $stage->start_date && $stage->end_date
                        ? self::durationDays($stage->start_date, $stage->end_date)
                        : null,
                    'depends_on_project_stage_id' => $stage->depends_on_project_stage_id,
                ])->all(),
            ];
        });
    }

    public static function durationDays(Carbon|string $start, Carbon|string $end): int
    {
        $start = $start instanceof Carbon ? $start : Carbon::parse($start)->startOfDay();
        $end = $end instanceof Carbon ? $end : Carbon::parse($end)->startOfDay();

        return $start->diffInDays($end) + 1;
    }

    private function validateStageDates($stages, array $effective, Carbon $projectStart, Carbon $projectEnd): void
    {
        foreach ($effective as $id => $dates) {
            $stage = $stages[$id];

            if ($dates['start'] && $dates['end'] && $dates['end']->lt($dates['start'])) {
                throw new ValidationException(
                    "Tanggal selesai stage '{$stage->name}' harus setelah tanggal mulainya.",
                    ['stages' => ["Stage '{$stage->name}': tanggal selesai harus setelah tanggal mulai."]]
                );
            }

            if (($dates['start'] && ($dates['start']->lt($projectStart) || $dates['start']->gt($projectEnd)))
                || ($dates['end'] && ($dates['end']->lt($projectStart) || $dates['end']->gt($projectEnd)))) {
                throw new ValidationException(
                    "Tanggal stage '{$stage->name}' harus berada dalam periode proyek.",
                    ['stages' => ["Stage '{$stage->name}': tanggal harus berada dalam periode proyek."]]
                );
            }
        }
    }

    private function validateDependencies($stages, array $effective): void
    {
        foreach ($effective as $id => $dates) {
            $dependsOn = $dates['depends_on'];

            if ($dependsOn === null) {
                continue;
            }

            if (! $stages->has($dependsOn)) {
                throw new ValidationException(
                    'Dependency stage tidak valid.',
                    ['stages' => ["Stage '{$stages[$id]->name}': dependency stage id {$dependsOn} bukan bagian dari proyek."]]
                );
            }

            $depStart = $effective[$dependsOn]['start'];
            $ownStart = $dates['start'];

            if ($depStart && $ownStart && $depStart->gt($ownStart)) {
                throw new ValidationException(
                    "Stage '{$stages[$id]->name}' tidak dapat dimulai sebelum dependency-nya.",
                    ['stages' => ["Stage '{$stages[$id]->name}' harus dimulai setelah '{$stages[$dependsOn]->name}'."]]
                );
            }
        }

        StageDependencies::assertAcyclic(
            collect($effective)->mapWithKeys(fn ($dates, $id) => [$id => $dates['depends_on']])->all()
        );
    }
}
