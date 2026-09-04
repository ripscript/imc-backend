<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailHeroCarousel extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'image_path',
        'tag',
        'title',
        'subtitle',
        'button_text',
        'button_link',
        'order',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'content_id',
    ];
}
