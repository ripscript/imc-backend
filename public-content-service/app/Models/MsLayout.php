<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsLayout extends Model
{
    use HasFactory;
    protected $hidden = ['id', 'created_by', 'updated_by', 'created_at', 'updated_at'];

    protected $table = 'ms_layout';
}
