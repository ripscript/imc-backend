<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentTag;
use App\Models\MsLayout;
use App\Models\Project;
use App\Models\ProjectAttachment;
use App\Services\DTOs\ProjectDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Support\CustomFieldValues;
use App\Support\ProjectAttachments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    protected SaveContentsService $saveContentService;

    protected GlobalSearchService $globalSearchService;

    protected ParentContentService $parentContentService;

    public function __construct(
        protected QueryService $queryService
    ) {
        $this->saveContentService = new SaveContentsService;
        $this->globalSearchService = new GlobalSearchService;
        $this->parentContentService = new ParentContentService;
    }

    public function sideMenu()
    {
        return $this->parentContentService->getParentByType('project');
    }

    /**
     * Sortable project columns. `client`, `stage`, and `progress` are
     * derived values sorted in memory; the rest sort in SQL.
     */
    public const SORTABLE_COLUMNS = ['title', 'project_no', 'client', 'stage', 'progress', 'status', 'updated_at'];

    public function index(QueryFilterDTO $filter, ?int $authUserId = null)
    {
        $query = Project::query()
            ->with(['client', 'partner', 'category', 'pic', 'attachments', 'stages.tasks']);

        if (! empty($filter->status)) {
            $query->where('status', $filter->status);
        }
        if (! empty($filter->client_id)) {
            $query->where('client_id', $filter->client_id);
        }
        if (! empty($filter->category_id)) {
            $query->where('category_id', $filter->category_id);
        }
        if ($filter->my && $authUserId) {
            $query->where(function ($q) use ($authUserId) {
                $q->where('pic_user_id', $authUserId)
                    ->orWhereHas('members', fn ($m) => $m->where('user_id', $authUserId)->where('status', 'active'));
            });
        }

        $query = $this->queryService->search($query, $filter, ['title', 'project_no']);

        if (in_array($filter->sort_by, ['client', 'stage', 'progress'], true)) {
            $projects = $query->get()->each(fn (Project $project) => $project->applyDerivedAttributes());

            return $this->queryService->paginateCollection(
                $this->sortByDerived($projects, $filter->sort_by, $filter->sort_order),
                $filter
            );
        }

        $sortBy = in_array($filter->sort_by, self::SORTABLE_COLUMNS, true) ? $filter->sort_by : 'updated_at';
        $sortOrder = strtolower($filter->sort_order ?? 'asc') === 'desc' ? 'desc' : 'asc';
        if (! $filter->sort_by) {
            $sortOrder = 'desc';
        }
        $query->orderBy($sortBy, $sortOrder);

        $result = $this->queryService->paginate($query, $filter);
        collect($result['data'])->each(fn (Project $project) => $project->applyDerivedAttributes());

        return $result;
    }

    protected function sortByDerived($projects, string $sortBy, ?string $sortOrder)
    {
        $descending = strtolower($sortOrder ?? 'asc') === 'desc';

        $sorted = match ($sortBy) {
            'client' => $projects->sortBy(fn (Project $p) => strtolower($p->client?->name ?? "\u{FFFF}")),
            'stage' => $projects->sortBy(fn (Project $p) => $p->getAttribute('current_stage')['sequence'] ?? PHP_INT_MAX),
            default => $projects->sortBy(fn (Project $p) => $p->getAttribute('progress') ?? 0),
        };

        return $descending ? $sorted->reverse() : $sorted;
    }

    public function detail(int $projectId)
    {
        $project = Project::with(['client', 'partner', 'category', 'workflow', 'pic', 'attachments', 'layout'])
            ->findOrFail($projectId);

        $project->load(['contentAdditionalImage', 'contentAdditionalVideo', 'contentAdditionalFile', 'contentAdditionalLink', 'contentAdditionalText']);
        $project->load(['stages.tasks', 'stages.pic']);
        $project->tag_id = ContentTag::where([
            'content_type' => Project::MORPH_TYPE,
            'content_id' => $project->id,
        ])->pluck('tag_id')->toArray();
        $project->applyAdditionalContent($project);
        $project->applyDerivedAttributes();
        $project->setAttribute('custom_fields', CustomFieldValues::forEntity('project', $project->id));

        return $project;
    }

    public function resolveContentId(?int $contentId): int
    {
        if (! empty($contentId)) {
            return $contentId;
        }

        $resolved = Content::where('type', 'project')->orderBy('id')->value('id');

        if (empty($resolved)) {
            throw ValidationException::withMessages([
                'content_id' => ['Konten bertipe project belum tersedia.'],
            ]);
        }

        return $resolved;
    }

    protected function resolveLayoutId(?int $layoutId): ?int
    {
        if (! empty($layoutId)) {
            return $layoutId;
        }

        try {
            return MsLayout::findByType('project_detail')->id;
        } catch (\Throwable $e) {
            return MsLayout::where('type', 'project')->value('id');
        }
    }

    protected function uniqueSlug(string $base, int $contentId, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $candidate = $slug;
        $counter = 2;

        while (Project::where('content_id', $contentId)->where('slug', $candidate)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $candidate = $slug.'-'.$counter++;
        }

        return $candidate;
    }

    public function create(ProjectDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            $contentId = $this->resolveContentId($dto->content_id);
            $title = $dto->title ?? $dto->name;

            if (empty($title)) {
                throw ValidationException::withMessages([
                    'title' => ['Judul proyek wajib diisi.'],
                ]);
            }

            $project = new Project;
            $project->content_id = $contentId;
            $project->title = $title;
            $project->slug = ! empty($dto->slug) ? $dto->slug : $this->uniqueSlug($title, $contentId);
            $project->description = $dto->description;
            $project->thumbnail_path = $dto->thumbnail_path;
            $project->publish_date = $dto->publish_date;
            $project->is_publicated = $dto->is_publicated ?? (($dto->status ?? 'draft') === 'active');
            $project->layout_id = $this->resolveLayoutId($dto->layout_id);
            $project->kategori_content_id = $dto->kategori_content_id;
            $project->duration = $dto->duration;
            $project->location = $dto->location;
            $project->budget = $dto->budget;
            $project->organization_name = $dto->organization_name;
            $project->organization_address = $dto->organization_address;
            $project->client_id = $dto->client_id;
            $project->partner_id = $dto->partner_id;
            $project->category_id = $dto->category_id;
            $project->workflow_id = $dto->workflow_id;
            $project->pic_user_id = $dto->pic_user_id;
            $project->status = $dto->status ?? 'draft';
            $project->progress = $dto->progress ?? 0;
            $project->start_date = $dto->start_date;
            $project->target_end_date = $dto->target_end_date;
            $project->created_by = $dto->created_by;
            $project->updated_by = $dto->updated_by;
            $project->save();

            if (empty($project->project_no)) {
                $project->project_no = ProjectCodeGenerator::project($project->id);
                $project->save();
            }

            $this->syncAttachments($project, $dto->attachments ?? []);
            $this->saveContentService->syncTags(Project::MORPH_TYPE, $project->id, $dto->tag_id ?? [], $dto->created_by);
            $this->saveContentService->storeAdditionalContent(Project::MORPH_TYPE, $project, $dto->toArray(), $dto->created_by);

            if ($project->is_publicated) {
                $this->globalSearchService->insertProject($project->id);
            }

            $project->load(['client', 'partner', 'category', 'workflow', 'pic', 'attachments', 'stages.tasks']);
            $project->setAttribute('custom_fields', CustomFieldValues::forEntity('project', $project->id));

            return $project->applyDerivedAttributes();
        });
    }

    public function update(int $projectId, ProjectDTO $dto)
    {
        return DB::transaction(function () use ($projectId, $dto) {
            $project = Project::findOrFail($projectId);

            if ($dto->content_id !== null) {
                $project->content_id = $dto->content_id;
            }
            if ($dto->title !== null || $dto->name !== null) {
                $project->title = $dto->title ?? $dto->name;
            }
            if ($dto->slug !== null) {
                $project->slug = $dto->slug;
            }
            if ($dto->description !== null) {
                $project->description = $dto->description;
            }
            if ($dto->thumbnail_path !== null) {
                $project->thumbnail_path = $dto->thumbnail_path;
            }
            if ($dto->publish_date !== null) {
                $project->publish_date = $dto->publish_date;
            }
            if ($dto->is_publicated !== null) {
                $project->is_publicated = $dto->is_publicated;
            }
            if ($dto->layout_id !== null) {
                $project->layout_id = $dto->layout_id;
            }
            if ($dto->kategori_content_id !== null) {
                $project->kategori_content_id = $dto->kategori_content_id;
            }
            if ($dto->duration !== null) {
                $project->duration = $dto->duration;
            }
            if ($dto->location !== null) {
                $project->location = $dto->location;
            }
            if ($dto->budget !== null) {
                $project->budget = $dto->budget;
            }
            if ($dto->organization_name !== null) {
                $project->organization_name = $dto->organization_name;
            }
            if ($dto->organization_address !== null) {
                $project->organization_address = $dto->organization_address;
            }
            if ($dto->client_id !== null) {
                $project->client_id = $dto->client_id;
            }
            if ($dto->partner_id !== null) {
                $project->partner_id = $dto->partner_id;
            }
            if ($dto->category_id !== null) {
                $project->category_id = $dto->category_id;
            }
            if ($dto->workflow_id !== null) {
                $project->workflow_id = $dto->workflow_id;
            }
            if ($dto->pic_user_id !== null) {
                $project->pic_user_id = $dto->pic_user_id;
            }
            if ($dto->status !== null) {
                $project->status = $dto->status;
            }
            if ($dto->progress !== null) {
                $project->progress = $dto->progress;
            }
            if ($dto->start_date !== null) {
                $project->start_date = $dto->start_date;
            }
            if ($dto->target_end_date !== null) {
                $project->target_end_date = $dto->target_end_date;
            }
            $project->updated_by = $dto->updated_by;
            $picChanged = $project->isDirty('pic_user_id');
            $project->save();

            if ($picChanged && $project->pic_user_id) {
                (new PmProjectMemberService)->syncPic($project->id, (int) $project->pic_user_id, $dto->updated_by);
            }

            if (! empty($dto->deleted_attachment_ids)) {
                ProjectAttachment::where('project_id', $project->id)
                    ->whereIn('id', $dto->deleted_attachment_ids)
                    ->delete();
            }

            if (! empty($dto->attachments)) {
                $this->syncAttachments($project, $dto->attachments);
            }

            $this->saveContentService->syncTags(Project::MORPH_TYPE, $project->id, $dto->tag_id ?? [], $dto->updated_by);
            $this->saveContentService->storeAdditionalContent(Project::MORPH_TYPE, $project, $dto->toArray(), $dto->updated_by);

            if ($project->is_publicated) {
                $this->globalSearchService->insertProject($project->id);
            } else {
                $this->globalSearchService->delete(Project::MORPH_TYPE, $project->id);
            }

            $project->load(['client', 'partner', 'category', 'workflow', 'pic', 'attachments', 'stages.tasks']);
            $project->setAttribute('custom_fields', CustomFieldValues::forEntity('project', $project->id));

            return $project->applyDerivedAttributes();
        });
    }

    public function destroy(int $projectId)
    {
        $project = Project::findOrFail($projectId);

        if ($project->status !== 'draft') {
            throw new \App\Exceptions\Domain\ValidationException(
                'Hanya proyek berstatus draft yang dapat dihapus.',
                null,
                422
            );
        }

        $project->delete();

        return true;
    }

    protected function syncAttachments(Project $project, array $items): void
    {
        foreach (ProjectAttachments::normalize($items) as $item) {
            if (empty($item['file_url'])) {
                continue;
            }

            $project->attachments()->create([
                'file_url' => $item['file_url'],
                'file_name' => $item['file_name'] ?? basename(parse_url($item['file_url'], PHP_URL_PATH) ?? $item['file_url']),
                'file_size_kb' => isset($item['file_size_kb']) ? (int) $item['file_size_kb'] : null,
                'created_by' => $project->created_by ?? $project->updated_by,
            ]);
        }
    }
}
