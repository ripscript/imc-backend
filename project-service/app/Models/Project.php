<?php

namespace App\Models;

use App\Services\GlobalSearchService;
use App\Traits\HasActivityLog;
use App\Traits\HasAdditionalContentMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasActivityLog, HasAdditionalContentMapping, HasFactory, SoftDeletes;

    public const MORPH_TYPE = 'App\Models\ContentDetailProject';

    protected $logName = 'Project';

    protected $table = 'content_detail_projects';

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'target_end_date' => 'date:Y-m-d',
        'publish_date' => 'date:Y-m-d',
        'activated_at' => 'datetime',
        'activation_requested_at' => 'datetime',
        'progress' => 'integer',
        'is_publicated' => 'boolean',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    public function client()
    {
        return $this->belongsTo(PmClient::class, 'client_id');
    }

    public function partner()
    {
        return $this->belongsTo(PmPartner::class, 'partner_id');
    }

    public function category()
    {
        return $this->belongsTo(PmProjectCategory::class, 'category_id');
    }

    public function workflow()
    {
        return $this->belongsTo(PmWorkflow::class, 'workflow_id');
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    /**
     * Admin that approved the activation (gate 1, specs/005).
     */
    public function activator()
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    /**
     * User that requested the activation.
     */
    public function activationRequester()
    {
        return $this->belongsTo(User::class, 'activation_requested_by');
    }

    public function layout()
    {
        return $this->belongsTo(MsLayout::class, 'layout_id');
    }

    public function attachments()
    {
        return $this->hasMany(ProjectAttachment::class, 'project_id');
    }

    public function stages()
    {
        return $this->hasMany(ProjectStage::class, 'project_id')->orderBy('sequence');
    }

    public function documents()
    {
        return $this->hasMany(PmDocument::class, 'project_id');
    }

    public function milestones()
    {
        return $this->hasMany(PmMilestone::class, 'project_id');
    }

    public function changeRequests()
    {
        return $this->hasMany(PmChangeRequest::class, 'project_id');
    }

    public function members()
    {
        return $this->hasMany(PmProjectMember::class, 'project_id');
    }

    public function approvalRequests()
    {
        return $this->hasMany(PmApprovalRequest::class, 'project_id');
    }

    /**
     * First stage (by sequence) that is not completed yet.
     * Returns null when the project has no stages or all are completed.
     */
    public function resolveCurrentStage(): ?ProjectStage
    {
        $stages = $this->relationLoaded('stages') ? $this->stages : $this->stages()->get();

        return $stages->first(fn (ProjectStage $stage) => $stage->status !== 'completed');
    }

    /**
     * Derived project progress: average of stage progress. Falls back to
     * the stored column when the project has no stages yet.
     */
    public function computeProgress(): int
    {
        $stages = $this->relationLoaded('stages') ? $this->stages : $this->stages()->get();

        if ($stages->isEmpty()) {
            return (int) ($this->getAttributes()['progress'] ?? 0);
        }

        return (int) round($stages->avg(fn (ProjectStage $stage) => $stage->calculateProgress()));
    }

    /**
     * Attach derived `current_stage` (+ `progress`) attributes so API
     * resources can render them without extra queries.
     */
    public function applyDerivedAttributes(): static
    {
        $current = $this->resolveCurrentStage();

        $this->setAttribute('current_stage', $current ? [
            'id' => $current->id,
            'name' => $current->name,
            'sequence' => $current->sequence,
            'status' => $current->status,
        ] : null);
        $this->setAttribute('progress', $this->computeProgress());

        return $this;
    }

    public function contentAdditionalImage()
    {
        return $this->morphMany(ContentAdditionalImage::class, 'content')->orderBy('order', 'asc');
    }

    public function contentAdditionalVideo()
    {
        return $this->morphMany(ContentAdditionalVideo::class, 'content')->orderBy('order', 'asc');
    }

    public function contentAdditionalLink()
    {
        return $this->morphMany(ContentAdditionalLink::class, 'content')->orderBy('order', 'asc');
    }

    public function contentAdditionalFile()
    {
        return $this->morphMany(ContentAdditionalFile::class, 'content')->orderBy('order', 'asc');
    }

    public function contentAdditionalText()
    {
        return $this->morphMany(ContentAdditionalText::class, 'content')->orderBy('order', 'asc');
    }

    public function getMorphClass()
    {
        return self::MORPH_TYPE;
    }

    protected static function booted()
    {
        static::deleting(function ($project) {
            $project->contentAdditionalImage()->delete();
            $project->contentAdditionalVideo()->delete();
            $project->contentAdditionalFile()->delete();
            $project->contentAdditionalLink()->delete();
            $project->contentAdditionalText()->delete();

            // Cascade soft-deletes through the PM aggregate (draft-only
            // deletion; DB-level cascades cover force deletes).
            foreach ($project->stages()->get() as $stage) {
                $stage->tasks()->get()->each(fn ($task) => $task->delete());
                $stage->deliverables()->get()->each(fn ($deliverable) => $deliverable->delete());
                $stage->documents()->get()->each(fn ($document) => $document->delete());
                $stage->delete();
            }

            $project->documents()->get()->each(fn ($document) => $document->delete());
            $project->milestones()->get()->each(fn ($milestone) => $milestone->delete());
            $project->changeRequests()->get()->each(fn ($changeRequest) => $changeRequest->delete());
            $project->members()->get()->each(fn ($member) => $member->delete());
            $project->approvalRequests()->get()->each(fn ($approvalRequest) => $approvalRequest->delete());
        });

        static::deleted(function ($project) {
            ContentTag::where('content_type', Project::MORPH_TYPE)
                ->where('content_id', $project->id)->delete();
            $service = new GlobalSearchService;
            $service->delete(Project::MORPH_TYPE, $project->id);
        });
    }
}
