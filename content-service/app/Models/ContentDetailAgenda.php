<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailAgenda extends Model
{
    use HasFactory, HasActivityLog;
    protected $guarded = ['id'];
    protected $logName = 'agenda';
}
