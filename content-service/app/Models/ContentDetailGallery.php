<?php

namespace App\Models;

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
        'date',
        'order',
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
            $service = new GlobalSearchService;
            $service->delete(ContentDetailGallery::class, $content->id);
        });
    }
}
