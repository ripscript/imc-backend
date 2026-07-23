<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsPerwakilan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_perwakilan';

    protected $fillable = ['name', 'is_active', 'created_by', 'updated_by'];
}
