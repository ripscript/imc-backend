<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailConsume extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'consume_id',
        'consume_type',
        'order',
        'created_by',
        'updated_by',
    ];
}
