<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailOrganizationalStructureGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'name',
        'order',
        'created_by',
        'updated_by',
    ];
}
