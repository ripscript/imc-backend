<?php

namespace App\Models;

use App\Services\GlobalSearchService;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailGallery extends Model
{
    use HasFactory, HasActivityLog;
    protected $guarded = ['id'];
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
            $service = new GlobalSearchService();
            $service->delete(ContentDetailGallery::class, $content->id);
        });
    }
}
