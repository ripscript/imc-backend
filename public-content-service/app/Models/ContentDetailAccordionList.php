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
        'order',
        'created_by',
        'updated_by',
    ];
    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content_detail_accordion_group_id'];
}
