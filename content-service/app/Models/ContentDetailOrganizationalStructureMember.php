<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailOrganizationalStructureMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
        'position',
        'image_url',
        'order',
        'created_by',
        'updated_by',
    ];
}
