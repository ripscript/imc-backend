<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailImageCarousel extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'image_url',
        'order',
        'created_by',
        'updated_by',
    ];
}
