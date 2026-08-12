<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailOrganizationalStructureMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_detail_organizational_structure_group_id',
        'name',
        'level',
        'position',
        'description',
        'image_path',
        'is_child',
        'have_child',
        'parent_id',
        'order',
        'created_by',
        'updated_by',
    ];
}
