<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailAgenda extends Model
{
    use HasActivityLog, HasFactory;

    protected $guarded = ['id'];

    protected $logName = 'agenda';
}
