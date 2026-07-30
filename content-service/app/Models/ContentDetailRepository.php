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
        'description',
        'file_url',
        'date',
        'order',
        'created_by',
        'updated_by',
    ];

    protected $logName = 'repositori';

    protected static function booted()
    {
        static::deleted(function ($content) {
            $service = new GlobalSearchService;
            $service->delete(ContentDetailRepository::class, $content->id);
        });
    }
}
