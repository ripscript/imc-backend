<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailAgenda extends Model
{
    use HasActivityLog, HasFactory;

    protected $fillable = [
        'content_id',
        'is_range',
        'datetime_start',
        'datetime_end',
        'event_name',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $logName = 'agenda';
}
