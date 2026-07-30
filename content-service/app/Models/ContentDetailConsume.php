<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailConsume extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'type',
        'title',
        'description',
        'file_url',
        'image_url',
        'order',
        'created_by',
        'updated_by',
    ];
}
