<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsTag extends Model
{
    use HasFactory;

    protected $table = 'ms_tags';

    protected $fillable = ['title', 'is_active', 'created_by', 'updated_by'];
}
