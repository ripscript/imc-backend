<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsLayout extends Model
{
    use HasFactory;

    protected $table = 'ms_layout';
    protected $fillable = ['title', 'is_custom', 'type'];
}
