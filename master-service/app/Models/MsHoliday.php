<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsHoliday extends Model
{
    use HasFactory, HasActivityLog;
    protected $table = 'ms_holidays';
    protected $guarded = ['id'];
    protected $logName = 'hari libur';
}
