<?php

namespace App\Models;

use App\Services\GlobalSearchService;
use App\Traits\HasAdditionalContentMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasAdditionalContentMapping, HasFactory, SoftDeletes;

    public const MORPH_TYPE = 'App\Models\ContentDetailProject';

    protected $table = 'content_detail_projects';

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'target_end_date' => 'date:Y-m-d',
        'publish_date' => 'date:Y-m-d',
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

    public function layout()
    {
        return $this->belongsTo(MsLayout::class, 'layout_id');
    }

    public function attachments()
    {
        return $this->hasMany(ProjectAttachment::class, 'project_id');
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
        });

        static::deleted(function ($project) {
            ContentTag::where('content_type', Project::MORPH_TYPE)
                ->where('content_id', $project->id)->delete();
            $service = new GlobalSearchService;
            $service->delete(Project::MORPH_TYPE, $project->id);
        });
    }
}
