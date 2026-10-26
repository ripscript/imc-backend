<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailOrganizationalStructureMember extends Model
{
    use HasFactory;
    protected $fillable = [
        'content_detail_organizational_structure_group_id',
        'parent_id',
        'name',
        'level',
        'position',
        'description',
        'image_path',
        'is_child',
        'have_child',
        'order',
        'created_by',
        'updated_by',
    ];
    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content_detail_organizational_structure_group_id', 'parent_id'];

    public function group()
    {
        return $this->belongsTo(ContentDetailOrganizationalStructureGroup::class, 'content_detail_organizational_structure_group_id');
    }
}
