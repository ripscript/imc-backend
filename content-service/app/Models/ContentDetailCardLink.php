<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailCardLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'title',
        'image_url',
        'description',
        'url',
        'order',
        'created_by',
        'updated_by',
    ];
}
