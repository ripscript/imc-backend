<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailGalleryList extends Model
{
    use HasFactory;

    protected $fillable = [
        'gallery_id',
        'title',
        'image_url',
        'description',
        'order',
        'created_by',
        'updated_by',
    ];
}
