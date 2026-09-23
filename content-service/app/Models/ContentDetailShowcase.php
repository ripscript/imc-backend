<?php

namespace App\Models;

use App\Services\GlobalSearchService;
use App\Traits\HasActivityLog;
use App\Traits\HasAdditionalContentMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailShowcase extends Model
{
    use HasActivityLog, HasAdditionalContentMapping, HasFactory;

    protected $fillable = [
        'content_id',
        'title',
        'slug',
        'description',
        'thumbnail_path',
        'is_publicated',
        'publish_date',
        'layout_id',
        'has_additional_image',
        'has_additional_video',
        'has_additional_file',
        'has_additional_link',
        'has_additional_text',
        'duration',
        'location',
        'start_date',
        'budget',
        'organization_address',
        'organization_name',
        'kategori_content_id',
        'created_by',
        'updated_by',
    ];

    protected $logName = 'showcase';

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

    protected static function booted()
    {
        static::deleting(function ($showcase) {
            $showcase->contentAdditionalImage()->delete();
            $showcase->contentAdditionalVideo()->delete();
            $showcase->contentAdditionalFile()->delete();
            $showcase->contentAdditionalLink()->delete();
            $showcase->contentAdditionalText()->delete();
        });

        static::deleted(function ($content) {
            ContentTag::where('content_type', ContentDetailShowcase::class)
                ->where('content_id', $content->id)->delete();
            $service = new GlobalSearchService;
            $service->delete(ContentDetailShowcase::class, $content->id);
        });
    }
}
