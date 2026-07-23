<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsAlasanKeberatan extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'ms_alasan_keberatan';

    protected $guarded = ['id'];

    protected $logName = 'alasan keberatan';
}
