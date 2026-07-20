<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsFaq extends Model
{
    use HasFactory, SoftDeletes, HasActivityLog;
    protected $table = 'ms_faq';
    protected $logName = 'FAQ';
}
