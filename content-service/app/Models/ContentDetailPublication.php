<?php

namespace App\Models;

use App\Services\GlobalSearchService;
use App\Traits\HasActivityLog;
use App\Traits\HasAdditionalContentMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailPublication extends Model
{
    use HasFactory, HasAdditionalContentMapping, HasActivityLog;
    protected $guarded = ['id'];
    protected $logName = 'publikasi';

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
        return $this->morphOne(ContentAdditionalText::class, 'content');
    }

    protected static function booted()
    {
        static::deleting(function ($publication) {
            $publication->contentAdditionalImage()->delete();
            $publication->contentAdditionalVideo()->delete();
            $publication->contentAdditionalFile()->delete();
            $publication->contentAdditionalLink()->delete();
            $publication->contentAdditionalText?->delete();
        });

        static::deleted(function ($content) {
            $service = new GlobalSearchService();
            $service->delete(ContentDetailPublication::class, $content->id);
        });
    }
}
