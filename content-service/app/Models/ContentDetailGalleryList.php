<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailGalleryList extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_detail_gallery_id',
        'title',
        'description',
        'image_path',
        'video_type',
        'video_path',
        'video_url',
        'order',
        'created_by',
        'updated_by',
    ];
}
