<?php

namespace App\Models;

use App\Models\ContentTag;
use App\Services\GlobalSearchService;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailGallery extends Model
{
    use HasActivityLog, HasFactory;

    protected $fillable = [
        'content_id',
        'title',
        'description',
        'slug',
        'is_publicated',
        'publish_date',
        'layout_id',
        'created_by',
        'updated_by',
    ];

    protected $logName = 'galeri';

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function list()
    {
        return $this->hasMany(ContentDetailGalleryList::class)->orderBy('order', 'asc');
    }

    protected static function booted()
    {
        static::deleted(function ($content) {
            ContentTag::where('content_type', ContentDetailGallery::class)
                ->where('content_id', $content->id)->delete();
            $service = new GlobalSearchService;
            $service->delete(ContentDetailGallery::class, $content->id);
        });
    }
}
