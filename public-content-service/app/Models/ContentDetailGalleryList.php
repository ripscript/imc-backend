<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailGalleryList extends Model
{
    use HasFactory;
    protected $fillable = [
        'content_detail_gallery_id',
        'image_path',
        'caption',
        'order',
        'created_by',
        'updated_by',
    ];
    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content_detail_gallery_id'];
}
