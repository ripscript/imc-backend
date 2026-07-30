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
        'title',
        'date',
        'time',
        'location',
        'description',
        'order',
        'created_by',
        'updated_by',
    ];

    protected $logName = 'agenda';
}
