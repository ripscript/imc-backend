<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsNegara extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'ms_negara';

    protected $guarded = ['id'];

    protected $logName = 'negara';
}
