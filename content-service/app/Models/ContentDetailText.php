<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailText extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'type',
        'title',
        'description',
        'order',
        'created_by',
        'updated_by',
    ];
}
