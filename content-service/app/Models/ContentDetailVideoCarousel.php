<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailVideoCarousel extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'title',
        'type',
        'url',
        'video_path',
        'order',
        'created_by',
        'updated_by',
    ];
}
