<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsTag extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'slug',
        'is_active',
        'created_by',
        'updated_by',
    ];
    protected $table = 'ms_tags';

    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'is_active'];
}
