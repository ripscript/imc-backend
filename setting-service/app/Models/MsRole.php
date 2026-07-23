<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsRole extends Model
{
    use HasFactory;

    protected $table = 'ms_roles';

    protected $fillable = ['name', 'is_active', 'created_by', 'updated_by'];
}
