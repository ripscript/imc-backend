<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailHeroCarousel extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'source_type',
        'source_id',
        'button_text',
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
