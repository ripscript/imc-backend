<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Models\PmChangeRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Gate 3 — change requests (specs/005).
 *
 * An admin records a change suggested by a client (`client_id`) as a JSON
 * diff (`proposed_changes`) over the project's configured fields. The fixed
 * approver role approves, then an admin applies the diff to the project.
 *
 * Status flow: `pending_approval -> approved | rejected`, then
 * `approved -> applied`. `requested_by` is the recording admin;
 * the suggesting client rides on `client_id` (client login is a future epic).
 */
class PmChangeRequestService
{
    /**
     * Project columns a CR may propose. `stages` carries per-stage date
     * overrides: `[{id, start_date?, end_date?}]`.
     */
    public const PROJECT_FIELDS = [
        'title',
        'description',
        'start_date',
        'target_end_date',
        'budget',
        'location',
        'duration',
        'organization_name',
        'organization_address',
        'client_id',
        'partner_id',
        'category_id',
        'pic_user_id',
    ];

    public function __construct(
        protected ?QueryService $queryService = null
    ) {}

    public function list(int $projectId, QueryFilterDTO $filter)
    {
        Project::findOrFail($projectId);

        $query = PmChangeRequest::with(['client', 'stage', 'requester', 'decider'])
            ->where('project_id', $projectId);

        if (! empty($filter->status)) {
            $query->where('status', $filter->status);
        }

        $query->orderBy('id', 'desc');

        return ($this->queryService ?? new QueryService)->paginate($query, $filter);
    }

    public function create(int $projectId, array $data, User $recorder): PmChangeRequest
    {
        $project = Project::findOrFail($projectId);

        $changes = $data['proposed_changes'] ?? [];
        $this->assertValidChanges($project, $changes);

        return DB::transaction(function () use ($project, $data, $changes, $recorder) {
            $sequence = PmChangeRequest::withTrashed()->where('project_id', $project->id)->count() + 1;

            return PmChangeRequest::create([
                'project_id' => $project->id,
                'project_stage_id' => $data['project_stage_id'] ?? null,
                'change_no' => 'CR-'.date('Y').'-'.$project->id.'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                'title' => $data['title'],
                'reason' => $data['reason'] ?? null,
                'impact_scope' => $data['impact_scope'] ?? null,
                'proposed_changes' => $changes,
                'client_id' => $data['client_id'] ?? null,
                'status' => 'pending_approval',
                'requested_by' => $recorder->id,
                'created_by' => $recorder->name,
                'updated_by' => $recorder->name,
            ]);
        });
    }

    public function approve(int $projectId, int $changeRequestId, User $actor): PmChangeRequest
    {
        return DB::transaction(function () use ($projectId, $changeRequestId, $actor) {
            $cr = $this->resolve($projectId, $changeRequestId);
            $this->assertStatus($cr, ['pending_approval']);

            $cr->status = 'approved';
            $cr->approved_by = $actor->id;
            $cr->approved_at = now();
            $cr->updated_by = $actor->name;
            $cr->save();

            return $cr->fresh()->load(['client', 'stage', 'requester', 'decider']);
        });
    }

    public function reject(int $projectId, int $changeRequestId, User $actor, string $notes): PmChangeRequest
    {
        return DB::transaction(function () use ($projectId, $changeRequestId, $actor, $notes) {
            $cr = $this->resolve($projectId, $changeRequestId);
            $this->assertStatus($cr, ['pending_approval']);

            $cr->status = 'rejected';
            $cr->approved_by = $actor->id;
            $cr->approved_at = now();
            $cr->notes = $notes;
            $cr->updated_by = $actor->name;
            $cr->save();

            return $cr->fresh()->load(['client', 'stage', 'requester', 'decider']);
        });
    }

    /**
     * Write an approved diff onto the project (and its stages), then mark
     * the CR applied.
     */
    public function apply(int $projectId, int $changeRequestId, User $actor): PmChangeRequest
    {
        return DB::transaction(function () use ($projectId, $changeRequestId, $actor) {
            $cr = $this->resolve($projectId, $changeRequestId);
            $this->assertStatus($cr, ['approved']);

            $project = Project::findOrFail($projectId);
            $changes = $cr->proposed_changes ?? [];
            $this->assertValidChanges($project, $changes);

            foreach ($changes as $field => $value) {
                if ($field === 'stages') {
                    continue;
                }
                $project->{$field} = $value;
            }
            $project->updated_by = $actor->name;
            $project->save();

            foreach ($changes['stages'] ?? [] as $entry) {
                $stage = $project->stages()->where('id', $entry['id'])->first();

                if (! $stage) {
                    throw new ValidationException(
                        'Stage pada change request tidak valid.',
                        ['proposed_changes.stages' => ["Stage id {$entry['id']} bukan bagian dari proyek ini."]]
                    );
                }

                foreach (['start_date', 'end_date'] as $field) {
                    if (array_key_exists($field, $entry)) {
                        $stage->{$field} = $entry[$field];
                    }
                }
                $stage->updated_by = $actor->name;
                $stage->save();
            }

            if ($project->is_publicated) {
                (new GlobalSearchService)->insertProject($project->id);
            }

            $cr->status = 'applied';
            $cr->applied_at = now();
            $cr->updated_by = $actor->name;
            $cr->save();

            return $cr->fresh()->load(['client', 'stage', 'requester', 'decider']);
        });
    }

    private function resolve(int $projectId, int $changeRequestId): PmChangeRequest
    {
        return PmChangeRequest::where('project_id', $projectId)
            ->where('id', $changeRequestId)
            ->firstOrFail();
    }

    private function assertStatus(PmChangeRequest $cr, array $allowed): void
    {
        if (! in_array($cr->status, $allowed, true)) {
            throw new ConflictHttpException('Change request tidak dapat diproses pada status '.$cr->status.'.');
        }
    }

    /**
     * Structural validation of the JSON diff: known keys, sane value
     * shapes. Existence of FK targets is enforced by request rules on
     * create and by the database on apply.
     */
    private function assertValidChanges(Project $project, mixed $changes): void
    {
        if (! is_array($changes) || empty($changes)) {
            throw new ValidationException(
                'Usulan perubahan wajib diisi.',
                ['proposed_changes' => ['Kirim minimal satu field yang diusulkan berubah.']]
            );
        }

        $allowed = array_merge(self::PROJECT_FIELDS, ['stages']);
        $unknown = array_diff(array_keys($changes), $allowed);

        if (! empty($unknown)) {
            throw new ValidationException(
                'Field usulan tidak dikenal.',
                ['proposed_changes' => array_map(fn ($f) => "Field '{$f}' tidak dapat diubah via change request.", array_values($unknown))]
            );
        }

        foreach (['start_date', 'target_end_date'] as $field) {
            if (array_key_exists($field, $changes) && ! $this->isDate($changes[$field])) {
                throw new ValidationException(
                    'Format tanggal usulan tidak valid.',
                    ["proposed_changes.{$field}" => ['Gunakan format YYYY-MM-DD.']]
                );
            }
        }

        if (array_key_exists('budget', $changes) && ! is_numeric($changes['budget'])) {
            throw new ValidationException(
                'Nilai usulan tidak valid.',
                ['proposed_changes.budget' => ['Budget harus berupa angka.']]
            );
        }

        if (array_key_exists('stages', $changes)) {
            if (! is_array($changes['stages'])) {
                throw new ValidationException(
                    'Usulan stage tidak valid.',
                    ['proposed_changes.stages' => ['Kirim array [{id, start_date?, end_date?}].']]
                );
            }

            foreach ($changes['stages'] as $index => $entry) {
                if (! is_array($entry) || empty($entry['id'])) {
                    throw new ValidationException(
                        'Usulan stage tidak valid.',
                        ["proposed_changes.stages.{$index}.id" => ['Setiap usulan stage wajib mencantumkan id.']]
                    );
                }

                foreach (['start_date', 'end_date'] as $field) {
                    if (array_key_exists($field, $entry) && ! $this->isDate($entry[$field])) {
                        throw new ValidationException(
                            'Format tanggal usulan tidak valid.',
                            ["proposed_changes.stages.{$index}.{$field}" => ['Gunakan format YYYY-MM-DD.']]
                        );
                    }
                }
            }
        }
    }

    private function isDate(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $parsed = date_create($value);

        return $parsed && $parsed->format('Y-m-d') === $value;
    }
}
