<?php

namespace App\Models;

use App\Services\GlobalSearchService;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailRepository extends Model
{
    use HasActivityLog, HasFactory;

    protected $fillable = [
        'content_id',
        'title',
        'file_path',
        'description',
        'thumbnail_path',
        'slug',
        'is_publicated',
        'publish_date',
        'layout_id',
        'created_by',
        'updated_by',
    ];

    protected $logName = 'repositori';

    public function contentTag()
    {
        return $this->morphMany(ContentTag::class, 'content');
    }

    protected static function booted()
    {
        static::deleted(function ($content) {
            ContentTag::where('content_type', ContentDetailRepository::class)
                ->where('content_id', $content->id)->delete();
            $service = new GlobalSearchService;
            $service->delete(ContentDetailRepository::class, $content->id);
        });
    }
}
