<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailAccordionList extends Model
{
    use HasFactory;
    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content_detail_accordion_group_id'];
}
