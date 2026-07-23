<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsTag extends Model
{
    use HasActivityLog, HasFactory;

    protected $table = 'ms_tags';

    protected $fillable = ['name', 'is_active', 'created_by', 'updated_by'];

    protected $logName = 'tag';
}
