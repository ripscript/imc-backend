<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentTag;
use App\Models\MsLayout;
use App\Models\Project;
use App\Models\ProjectAttachment;
use App\Services\DTOs\ProjectDTO;
use App\Services\DTOs\QueryFilterDTO;
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

    public function index(QueryFilterDTO $filter)
    {
        $query = Project::query()
            ->with(['client', 'partner', 'category', 'pic', 'attachments']);

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['title', 'project_no'], 'updated_at');
    }

    public function detail(string $projectNo)
    {
        $project = Project::with(['client', 'partner', 'category', 'workflow', 'pic', 'attachments', 'layout'])
            ->where('project_no', $projectNo)
            ->firstOrFail();

        $project->load(['contentAdditionalImage', 'contentAdditionalVideo', 'contentAdditionalFile', 'contentAdditionalLink', 'contentAdditionalText']);
        $project->tag_id = ContentTag::where([
            'content_type' => Project::MORPH_TYPE,
            'content_id' => $project->id,
        ])->pluck('tag_id')->toArray();
        $project->applyAdditionalContent($project);

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

            return $project->load(['client', 'partner', 'category', 'workflow', 'pic', 'attachments']);
        });
    }

    public function update(string $projectNo, ProjectDTO $dto)
    {
        return DB::transaction(function () use ($projectNo, $dto) {
            $project = Project::where('project_no', $projectNo)->firstOrFail();

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
            $project->save();

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

            return $project->load(['client', 'partner', 'category', 'workflow', 'pic', 'attachments']);
        });
    }

    public function destroy(string $projectNo)
    {
        $project = Project::where('project_no', $projectNo)->firstOrFail();

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

    protected function syncAttachments(Project $project, array $urls): void
    {
        $urls = array_values(array_filter($urls, fn ($url) => ! empty($url)));

        foreach ($urls as $url) {
            $project->attachments()->create([
                'file_url' => $url,
                'created_by' => $project->created_by,
            ]);
        }
    }
}
