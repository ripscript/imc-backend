<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailImageLink extends Model
{
    use HasFactory;
    protected $fillable = [
        'content_id',
        'title',
        'image_path',
        'url',
        'caption',
        'order',
        'created_by',
        'updated_by',
    ];
    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content_id'];
}
