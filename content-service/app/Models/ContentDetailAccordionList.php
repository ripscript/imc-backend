<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailAccordionList extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_detail_accordion_group_id',
        'title',
        'description',
        'file_path',
        'link',
        'order',
        'created_by',
        'updated_by',
    ];
}
